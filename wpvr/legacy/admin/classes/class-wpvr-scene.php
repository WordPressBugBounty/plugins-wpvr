<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Responsible for managing Scene tab on Setup meta box
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin/classes
 */

class WPVR_Scene {

    /**
     * Instance of WPVR_Hotspot class
     *
     * @var object
     * @since 8.0.0
     */
    protected $hotspot;

    /**
     * Instance of WPVR_Format class
     *
     * @var object
     * @since 8.0.0
     */
    protected $format;

    /**
     * Instance of WPVR_Validator class
     *
     * @var object
     * @since 8.0.0
     */
    private $validator;

    /**
     * Number of scene or hotspot item
     *
     * @var integer
     * @since 8.0.0
     */
    protected $data_limit;

    /**
     * Pro version license status
     *
     * @var string
     * @since 8.0.0
     */
    protected $status;


    function __construct()
    {
        $this->hotspot   = new WPVR_Hotspot();
        $this->format    = new WPVR_Format();
        $this->validator = new WPVR_Validator();

        $this->status = apply_filters( 'check_pro_license_status', $this->status );

        if ($this->status !== false && $this->status == 'valid') {
            $this->data_limit = 999999999;
        } else {
            $this->data_limit = 5;
        }
    }

    /**
     * Render Scene Settings Content
     *
     * @param array $postdata
     *
     * @return void
     * @since 8.0.0
     */
    public function render_scene($postdata)
    {
        ob_start();
        ?>

        <!-- Scene and Hotspot repeater -->
        <div class="scene-setup rex-pano-sub-tabs" data-limit="<?php echo esc_attr( $this->data_limit + 1 ); ?>">
            <?php $this->render_scene_repeater_list($postdata); ?>
        </div>

        <?php
//      ob_end_flush();
    }


    /**
     * Render scene setup data repeater list
     *
     * @param array $postdata
     *
     * @return void
     * @since 8.0.0
     */
    private function render_scene_repeater_list($postdata)
    {
        ob_start();
        ?>
        <nav class="rex-pano-tab-nav rex-pano-nav-menu scene-nav">
            <?php $this->render_nav_menu($postdata); // Will render scene navigation bar ?>
        </nav>

        <div data-repeater-list="scene-list" class="rex-pano-tab-content">

            <!-- Default empty repeater -->
            <div data-repeater-item class="single-scene rex-pano-tab" data-title="0" id="scene-0">
                <?php $this->render_default_repeater_item(); ?>
            </div>
            <!-- Empty repeater end -->

            <?php 
            if ( ! empty( $postdata['panodata']["scene-list"] ) && is_array( $postdata['panodata']["scene-list"] ) ) {
                $s = 1; $firstvalue = reset($postdata['panodata']["scene-list"]);
                $default_scene = $postdata['defaultscene'] ?? ($firstvalue['scene-id'] ?? '');
                foreach ($postdata['panodata']["scene-list"] as $pano_scene) {
                    if ( ! is_array( $pano_scene ) ) {
                        continue;
                    }
                    if ( ! isset( $pano_scene['dscene'] ) ) {
                        $pano_scene['dscene'] = ( ! empty( $default_scene ) && ( $pano_scene['scene-id'] ?? '' ) === $default_scene ) ? 'on' : 'off';
                    }
                    $is_active = ( ( $pano_scene['scene-id'] ?? '' ) === ( $firstvalue['scene-id'] ?? '' ) );
            ?>

                <div data-repeater-item  class="single-scene rex-pano-tab <?php if($is_active) { echo esc_attr('active'); }; ?>" data-title="1" id="scene-<?php echo esc_attr( $s ); ?>">
                    <?php $this->render_repeater_item_with_panodata($pano_scene, $s); ?>
                </div>

                <?php $s++; } 
            } ?>
        </div>
        <?php
//      ob_end_flush();
    }


    /**
     * Render scene nav menu
     *
     * @param array $postdata
     *
     * @return void
     * @since 8.0.0
     */
    private function render_nav_menu($postdata)
    {
        ob_start();
        ?>
        <ul>
            <?php 
            if ( ! empty( $postdata['panodata']["scene-list"] ) && is_array( $postdata['panodata']["scene-list"] ) ) {
                $i = 1; $firstvalue = reset($postdata['panodata']["scene-list"]);
                foreach ($postdata['panodata']["scene-list"] as $pano_scene) {
                    if ( ! is_array( $pano_scene ) ) {
                        continue;
                    }
                    $is_active = ( ( $pano_scene['scene-id'] ?? '' ) === ( $firstvalue['scene-id'] ?? '' ) );
            ?>

                <li class="<?php if ($is_active) {echo 'active';};?>">
            <span data-index="<?php echo esc_attr( $i ); ?>" data-href="#scene-<?php echo esc_attr( $i ); ?>">
              <i class="fa fa-image"></i>
            </span>
                </li>

                <?php $i++; } 
            } ?>
            <li class="add" data-repeater-create><span><i class="fa fa-plus-circle"></i></span></li>
        </ul>
        <?php
        ob_end_flush();
    }


    /**
     * Render repeater item for default scene
     *
     * @param int $data_limit
     *
     * @return void
     * @since 8.0.0
     */
    private function render_default_repeater_item()
    {
        ob_start();
        ?>
        <div class="active_scene_id"><p></p></div>
        <div class="scene-content">
            <?php $this->render_default_repeater_item_scene_content(); ?>
        </div>

        <!-- hotspot setup -->
        <div class="hotspot-setup rex-pano-sub-tabs" data-limit="<?php echo esc_attr( $this->data_limit ); ?>">
            <?php $this->hotspot->render_hotspot($s = 0, $h =1)?>
        </div>
        <button data-repeater-delete type="button" title="Delete Scene" class="delete-scene"><i class="far fa-trash-alt"></i></button>
        <?php
        ob_end_flush();
    }


    /**
     * Render repeater items while scene has panaromic data
     *
     * @param array $pano_scene
     * @param int $s scene number increment var
     *
     * @return void
     * @since 8.0.0
     */
    private function render_repeater_item_with_panodata($pano_scene, $s)
    {
        ob_start();
        ?>
        <div class="active_scene_id"><p></p></div>
        <div class="scene-content">
            <!--
              - Render repeater item scene content
              - If scene has panaromic data
            -->
            <?php $this->render_repeater_scene_content_with_data($pano_scene); ?>
        </div>
        <!--
          - Render repeater item hotspot content
        -->
        <?php $this->render_repeater_item_hotspot_content($pano_scene, $s); ?>

        <button data-repeater-delete type="button" title="Delete Scene" class="delete-scene"><i class="far fa-trash-alt"></i></button>
        <?php
        ob_end_flush();
    }


    /**
     * Render scene content for default repeater item
     *
     * @return void
     * @since 8.0.0
     */
    private function render_default_repeater_item_scene_content()
    {
        ob_start();
        ?>

        <h6 class="title"><i class="fa fa-cog"></i> <?php esc_html_e('Scene Settings','wpvr'); ?> </h6>

        <div class="scene-left">
            <?php WPVR_Meta_Field::render_scene_left_fields_empty_panodata(); ?>
        </div>

        <div class="scene-right">
            <?php do_action( 'wpvr_pro_scene_empty_right_fields' ) ?>
        </div>

        <?php
        ob_end_flush();
    }


    /**
     * Render repeater item scene content is scene has panaromic data
     *
     * @param mixed $dscene
     * @param mixed $scene_id
     * @param mixed $scene_photo
     *
     * @return void
     * @since 8.0.0
     */
    private function render_repeater_scene_content_with_data($pano_scene)
    {
        ob_start();
        ?>
        <h6 class="title"><i class="fa fa-cog"></i> <?php esc_html_e('Scene Settings','wpvr'); ?> </h6>

        <div class="scene-left">
            <?php WPVR_Meta_Field::render_scene_left_fields_with_panodata($pano_scene) ;?>
        </div>

        <div class="scene-right">
            <?php do_action( 'wpvr_pro_scene_right_fields', $pano_scene ) ?>
        </div>


        <?php
        ob_end_flush();
    }


    /**
     * Render repeater item hotspot content
     *
     * @param array $pano_hotspots
     * @param int $data_limit
     * @param int $s
     *
     * @return void
     * @since 8.0.0
     */
    private function render_repeater_item_hotspot_content($pano_scene, $s)
    {
        if (!empty($pano_scene['hotspot-list'])) { ?>
            <div class="hotspot-setup rex-pano-sub-tabs" data-limit="<?php echo esc_attr( $this->data_limit ); ?>">

                <?php $this->hotspot->render_hotspot_with_panodata($pano_scene['hotspot-list'], $s); //Render hotspot while scene has hotspot data ?>

            </div>
        <?php } else { ?>
            <div class="hotspot-setup rex-pano-sub-tabs" data-limit="<?php echo esc_attr( $this->data_limit ); ?>">

                <?php $this->hotspot->render_hotspot($s, $h = 1); //Render hotspot while scene has no hotspot data ?>

            </div>
        <?php }
    }


    /**
     * Update post meta data
     *
     * @param integer $postid
     * @param integer $panoid
     * @param boolean $is_publish_action
     *
     * @return void
     * @since 8.0.0
     */
    public function wpvr_update_meta_box($postid, $panoid, $is_publish_action = false)
    {
        $nonce  = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wpvr' ) ) {
            wp_die( 'Permission denied.' );
        }
        $panodata      = $this->format->prepare_panodata(isset($_POST['panodata']) ? wp_unslash( $_POST['panodata'] ) : ''); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $default_scene = $this->format->prepare_default_scene($panodata);
        $previewtext = isset($_POST['previewtext']) ? $this->validator->preview_text_validation(wp_unslash( $_POST['previewtext'] )) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        $gzoom       = $this->format->set_pro_checkbox_value(@wp_unslash( $_POST['gzoom'] ));
        $default_global_zoom = '';
        $max_global_zoom = '';
        $min_global_zoom = '';
        if ($gzoom == 'on') {
            $default_global_zoom = isset($_POST['dzoom']) ? wp_unslash( $_POST['dzoom'] ) : '';
            $max_global_zoom = isset($_POST['maxzoom']) ? wp_unslash( $_POST['maxzoom'] ) : '';
            $min_global_zoom = isset($_POST['minzoom']) ? wp_unslash( $_POST['minzoom'] ) : '';
        }

        $custom_control = isset($_POST['customcontrol']) ? wp_unslash( $_POST['customcontrol'] ) : null;

        $vrgallery            = $this->format->set_checkbox_value(@wp_unslash( $_POST['vrgallery'] ));
        $vrgallery_title      = $this->format->set_checkbox_value(@wp_unslash( $_POST['vrgallery_title'] ));
        $vrgallery_display    = $this->format->set_checkbox_value(@wp_unslash( $_POST['vrgallery_display'] ));
        $vrgallery_icon_size  = $this->format->set_checkbox_value(@wp_unslash( $_POST['vrgallery_icon_size'] ));

        $existing_panodata = get_post_meta( $postid, 'panodata', true );
        $existing_panodata = is_array( $existing_panodata ) ? $existing_panodata : array();
        $mouseZoom = isset( $_POST['mouseZoom'] )
            ? $this->format->set_pro_checkbox_value( wp_unslash( $_POST['mouseZoom'] ) )
            : ( $existing_panodata['mouseZoom'] ?? 'on' );
        $draggable = isset( $_POST['draggable'] )
            ? $this->format->set_pro_checkbox_value( wp_unslash( $_POST['draggable'] ) )
            : ( $existing_panodata['draggable'] ?? 'on' );
        $diskeyboard  = $this->format->set_pro_checkbox_value(@wp_unslash( $_POST['diskeyboard'] ));
        $keyboardzoom = $this->format->set_checkbox_value(@wp_unslash( $_POST['keyboardzoom'] ));
        $compass      = $this->format->set_checkbox_on_value(@wp_unslash( $_POST['compass'] ));
        //===Gyroscopre control===//
        $gyro = $this->format->set_pro_checkbox_value(@wp_unslash( $_POST['gyro'] ));
        if ($gyro == 'on') {
            if (!is_ssl()) {
                wp_send_json_error('<p><span>Warning:</span> Please add SSL to enable Gyroscope for WP VR. </p>');
                die();
            }
            $gyro = true;
            $deviceorientationcontrol = $this->format->set_checkbox_value(@wp_unslash( $_POST['deviceorientationcontrol'] ));
        } else {
            $gyro = false;
            $deviceorientationcontrol = false;
        }
        //===Gyroscopre control===//

        $autoload           = $this->format->set_checkbox_value(isset($_POST['autoload']) ? wp_unslash( $_POST['autoload'] ) : '');
        $control            = $this->format->set_checkbox_value(isset($_POST['control']) ? wp_unslash( $_POST['control'] ) : '');

        $scene_fade_duration = isset($_POST['scenefadeduration']) ? sanitize_text_field(wp_unslash( $_POST['scenefadeduration'] )) : '';
        $preview = isset($_POST['preview']) ? esc_url(wp_unslash( $_POST['preview'] )) : '';
        $rotation = isset($_POST['rotation']) ? sanitize_text_field(wp_unslash( $_POST['rotation'] )) : '';
        $autorotation = isset($_POST['autorotation']) ? sanitize_text_field(wp_unslash( $_POST['autorotation'] )) : '';

        $autorotationinactivedelay = isset($_POST['autorotationinactivedelay']) ? sanitize_text_field(wp_unslash( $_POST['autorotationinactivedelay'] )) : '';
        $autorotationstopdelay = isset($_POST['autorotationstopdelay']) ? sanitize_text_field(wp_unslash( $_POST['autorotationstopdelay'] )) : '';

        //===generic form===//
        $genericform = sanitize_text_field(isset($_POST['genericform']) ? wp_unslash( $_POST['genericform'] ) : 'off');
        $genericformshortcode = isset($_POST['genericformshortcode']) ? sanitize_text_field(wp_unslash( $_POST['genericformshortcode'] )) : '' ;
        $genericformicon = isset($_POST['genericformicon'])
            ? sanitize_text_field(wp_unslash($_POST['genericformicon']))
            : ($existing_panodata['genericformicon'] ?? 'fab fa-wpforms');
        $genericformiconcolor = isset($_POST['genericformiconcolor'])
            ? sanitize_hex_color(wp_unslash($_POST['genericformiconcolor']))
            : ($existing_panodata['genericformiconcolor'] ?? '#f7fffb');
        $genericformiconcolor = $genericformiconcolor ?: '#f7fffb';
        //===generic form===//


        if(isset($_POST['rotation']) && sanitize_text_field(wp_unslash( $_POST['rotation'] )) === 'on' ) {
            $this->validator->basic_setting_validation($autorotationinactivedelay, $autorotationstopdelay);   // Basic setting error control and validation //
        }

        //===Company Logo===//
        $cpLogoSwitch  = isset($_POST['cpLogoSwitch']) ? wp_unslash( $_POST['cpLogoSwitch'] ) : 'off';
        $cpLogoImg     = isset($_POST['cpLogoImg']) ? wp_unslash( $_POST['cpLogoImg'] ) : '';
        $cpLogoContent = isset($_POST['cpLogoContent']) ? sanitize_text_field(wp_unslash( $_POST['cpLogoContent'] )) : '';
        //===Company Logo===//

        //===Explainer video===//
        $explainerSwitch = isset($_POST['explainerSwitch']) ? wp_unslash( $_POST['explainerSwitch'] ) : 'off';
        $explainerContent = '';
        $explainerContent = isset($_POST['explainerContent']) ? wp_unslash( $_POST['explainerContent'] ) : '';
        //===Explainer video===//


        $scene_fade_duration = '';
        $scene_fade_duration = isset($_POST['scenefadeduration']) ? sanitize_text_field(wp_unslash( $_POST['scenefadeduration'] )) : '';

        // Only validate when publishing, not when saving drafts
        if ($is_publish_action) {
            $this->validator->scene_validation($panodata);                                                    // Scene content error control and validation //

            $this->validator->empty_scene_validation($panodata);                                              // Empty scene content error control and validation //

            $this->validator->duplicate_hotspot_validation($panodata);                                        // Duplicate error control and validation //
        }

        $panodata = $this->format->remove_empty_scene_and_hotspot($panodata);                             // Remove Empty scene and hotspot //

        //===audio===//
        $bg_music          = isset($_POST['bg_music']) ? sanitize_text_field(wp_unslash( $_POST['bg_music'] )) : 'off';
        $bg_music_url      = isset($_POST['bg_music_url']) ? esc_url_raw(wp_unslash( $_POST['bg_music_url'] )) : '';
        $autoplay_bg_music = isset($_POST['autoplay_bg_music']) ? sanitize_text_field(wp_unslash( $_POST['autoplay_bg_music'] )) : 'off';
        $loop_bg_music     = isset($_POST['loop_bg_music']) ? sanitize_text_field(wp_unslash( $_POST['loop_bg_music'] )) : 'off';
        if ($bg_music == 'on') {
            if (empty($bg_music_url)) {
                wp_send_json_error('<p><span>Warning:</span> Please add an audio file as you enabled audio for this tour </p>');
                die();
            }
        }
        //===audio===//
        $advanced_control = array(
            'keyboardzoom'              => $keyboardzoom,
            'diskeyboard'               => $diskeyboard,
            'draggable'                 => $draggable,
            'mouseZoom'                 => $mouseZoom,
            'gyro'                      => $gyro,
            'deviceorientationcontrol'  => $deviceorientationcontrol,
            'compass'                   => $compass,
            'vrgallery'                 => $vrgallery,
            'vrgallery_title'           => $vrgallery_title,
            'vrgallery_display'         => $vrgallery_display,
            'vrgallery_icon_size'       => $vrgallery_icon_size,
            'bg_music'                  => $bg_music,
            'bg_music_url'              => $bg_music_url,
            'autoplay_bg_music'         => $autoplay_bg_music,
            'loop_bg_music'             => $loop_bg_music,
            'cpLogoSwitch'              => $cpLogoSwitch,
            'cpLogoImg'                 => $cpLogoImg,
            'cpLogoContent'             => $cpLogoContent,
            'hfov'                      => $default_global_zoom,
            'maxHfov'                   => $max_global_zoom,
            'minHfov'                   => $min_global_zoom,
            'explainerSwitch'           => $explainerSwitch,
            'explainerContent'          => $explainerContent,
        );

        $pano_array = array();
        $pano_array = array(
            "panoid" => $panoid,
            "autoLoad" => $autoload,
            "showControls" => $control,
            "customcontrol" => $custom_control,
            "autoRotate" => $autorotation,
            "autoRotateInactivityDelay" => $autorotationinactivedelay,
            "autoRotateStopDelay" => $autorotationstopdelay,
            "genericform" => $genericform,
            "genericformshortcode" => $genericformshortcode,
            "genericformicon" => $genericformicon,
            "genericformiconcolor" => $genericformiconcolor,
            "preview" => $preview,
            "defaultscene" => $default_scene,
            "scenefadeduration" => $scene_fade_duration,
            "panodata" => $panodata,
            "previewtext" => $previewtext);
        $pano_array = apply_filters( 'prepare_scene_pano_array_with_pro_version', $pano_array, $_POST, $advanced_control );
        $pano_array = $this->format->prepare_rotation_wrapper_data($pano_array, $rotation);
        // Prepare tour rotation wrapper data /
        update_post_meta($postid, 'panodata', $pano_array);
        do_action( 'wpvr_hotspot_saved', $postid, $pano_array );
        $response = array(
            'success'   => true,
            'data'  => array(
                'post_ID' => $postid,
                'post_status' => get_post_status($postid)
            )
        );

        do_action( 'wpvr_tour_settings_saved', $postid );


        /**
         * Delete orphaned analytics data
         *
         * @param integer $postid POST ID
         *
         * @return void
         * @since 8.5.16
         */
        do_action('wpvr_pro_delete_orphaned_analytics_data', $postid);

        wp_send_json($response);
        die();

    }


    /**
     * Allow iframe but disallow script tags in user input
     * @since 8.5.16
     */
    public function wpvr_sanitize_iframe_only( $input ) {
        if ( function_exists( 'wpvr_sanitize_iframe_only' ) ) {
            return wpvr_sanitize_iframe_only( $input );
        }

        // Start with standard allowed post HTML (p, a, strong, etc.)
        $allowed_tags = wp_kses_allowed_html( 'post' );

        // Explicitly allow <iframe> with specific safe attributes
        $allowed_tags['iframe'] = array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'title'           => true,
            'frameborder'     => true,
            'allow'           => true,
            'allowfullscreen' => true,
            'referrerpolicy'  => true,
        );

        // Sanitize input
        return wp_kses( $input, $allowed_tags );
    }



    /**
     * Responsible for showing Scene Preview
     *
     * @param string $panoid
     * @param string $panovideo
     *
     * @return wp_send_json_success
     * @since 8.0.0
     */
    public function wpvr_scene_preview($panoid, $panovideo)
    {
      $nonce  = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
      if ( ! wp_verify_nonce( $nonce, 'wpvr' ) ) {
          wp_die( 'Permission denied.' );
      }
      $panodata     = $this->format->prepare_panodata( isset( $_POST['panodata'] ) ? wp_unslash( $_POST['panodata'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

      $control      = $this->format->set_checkbox_value(wp_unslash( $_POST['control'] ));
      $autoload     = $this->format->set_checkbox_value(wp_unslash( $_POST['autoload'] ));

      $compass      = $this->format->set_checkbox_on_value(@wp_unslash( $_POST['compass'] ));
      $mouseZoom    = $this->format->set_checkbox_value(@wp_unslash( $_POST['mouseZoom'] ));
      $draggable = $this->format->set_checkbox_value(wp_unslash( $_POST['draggable'] ) ?? null);
      $gzoom        = $this->format->set_pro_checkbox_value(@wp_unslash( $_POST['gzoom'] ));
      $diskeyboard  = $this->format->set_checkbox_value(@wp_unslash( $_POST['diskeyboard'] ));
      $keyboardzoom = $this->format->set_checkbox_value(@wp_unslash( $_POST['keyboardzoom'] ));

      $gyro = $this->format->set_checkbox_on_value(@wp_unslash( $_POST['gyro'] ));
      $deviceorientationcontrol = $this->format->set_checkbox_value(@wp_unslash( $_POST['deviceorientationcontrol'] ));

      $floor_plan_enabler = $this->format->set_pro_checkbox_value(@wp_unslash( $_POST['wpvr_floor_plan_enabler'] ));
      $floor_plan_image = isset($_POST['wpvr_floor_plan_image']) ? wp_unslash( $_POST['wpvr_floor_plan_image'] ) : '';

      $scene_fade_duration       = sanitize_text_field(wp_unslash( $_POST['scenefadeduration'] ));
      $preview                   = esc_url(wp_unslash( $_POST['preview'] ));

      $default_scene = '';

      $rotation                  = sanitize_text_field(wp_unslash( $_POST['rotation'] ));
      $autorotation              = sanitize_text_field(wp_unslash( $_POST['autorotation'] ));
      $autorotationinactivedelay = sanitize_text_field(wp_unslash( $_POST['autorotationinactivedelay'] ));
      $autorotationstopdelay     = sanitize_text_field(wp_unslash( $_POST['autorotationstopdelay'] ));

      $default_global_zoom = '';
      $max_global_zoom     = '';
      $min_global_zoom     = '';
      if ($gzoom == 'on') {
          $default_global_zoom = wp_unslash( $_POST['dzoom'] );
          $max_global_zoom     = wp_unslash( $_POST['maxzoom'] );
          $min_global_zoom     = wp_unslash( $_POST['minzoom'] );
      }
    
      $default_scene = $this->format->prepare_default_scene($panodata);

        if(isset($_POST['rotation']) && sanitize_text_field(wp_unslash( $_POST['rotation'] )) === 'on' ) {
            $this->validator->basic_setting_validation($autorotationinactivedelay, $autorotationstopdelay);   // Basic setting error control and validation //
        }
      $this->validator->scene_validation($panodata);                                                  // Scene content error control and validation //

      $this->validator->empty_scene_validation($panodata);                                            // Empty scene content error control and validation //
  
      $this->validator->duplicate_hotspot_validation($panodata);
      if($floor_plan_enabler == 'on'){
          $this->validator->empty_floor_plan_image_validation($floor_plan_image);
      }

      $default_data = array();
      if ($gzoom == 'on') {
          $default_data = array("firstScene" => $default_scene, "sceneFadeDuration" => $scene_fade_duration, "hfov" => $default_global_zoom, "maxHfov" => $max_global_zoom, "minHfov" => $min_global_zoom);
      } else {
          $default_data = array("firstScene" => $default_scene, "sceneFadeDuration" => $scene_fade_duration);
      }

      $scene_data = $this->format->prepare_scene_data_for_preview($panodata);
      
      
      $pano_id_array = array();
      $pano_id_array = array("panoid" => $panoid);
      $pano_response = array();
      $pano_response = array("autoLoad" => $autoload, "defaultZoom" => $default_global_zoom, "minZoom" => $min_global_zoom, "maxZoom" => $max_global_zoom, "showControls" => $control, "compass" => $compass, "orientationOnByDefault" => $deviceorientationcontrol, "mouseZoom" => $mouseZoom, "draggable" => $draggable, "disableKeyboardCtrl" => $diskeyboard, 'keyboardZoom' => $keyboardzoom, "preview" => $preview, "autoRotate" => $autorotation, "autoRotateInactivityDelay" => $autorotationinactivedelay, "autoRotateStopDelay" => $autorotationstopdelay, "default" => $default_data, "scenes" => $scene_data);
      
      $pano_response = $this->format->prepare_rotation_wrapper_data($pano_response, $rotation);

        $is_pro = apply_filters('is_wpvr_pro_active',false);
        $status  = get_option('wpvr_edd_license_status');
        $pano_floor_plan = array();
        $call_to_action = array();
        if ($status !== false &&  'valid' == $status  && $is_pro) {
            $pano_floor_plan = array(
                "floor_plan_tour_enabler" => $floor_plan_enabler,
                "floor_plan_attachment_url" => $floor_plan_image
            );
            if ( 'on' === $floor_plan_enabler ) {
                do_action( 'wpvr_floor_plan_configured', $postid, $pano_floor_plan );
            }
            $call_to_action = array(
                'button_enable' =>sanitize_text_field(wp_unslash( $_POST['callToAction'] )),
                'button_text' =>sanitize_text_field(wp_unslash( $_POST['buttontext'] )),
                'button_url' =>sanitize_text_field(wp_unslash( $_POST['buttonurl'] ))
            );
        }

        $response = array();
        $response = array($pano_id_array, $pano_response, $panovideo,$pano_floor_plan,$call_to_action);
        wp_send_json_success($response);
    }


    /**
     * Render shortcode for scene and hotspot post data
     *
     * @param array $postdata
     * @param string $panoid
     * @param integer $id
     * @param mixed $radius
     * @param mixed $width
     * @param mixed $height
     *
     * @return string
     * @since 8.0.0
     */
    public function render_scene_shortcode($postdata, $panoid, $id, $radius, $width, $height, $mobile_height)
    {
        if ( function_exists( 'wpvr_enqueue_frontend_scripts' ) ) {
            wpvr_enqueue_frontend_scripts( 'scene' );
        }
        $postdata = is_array( $postdata ) ? wpvr_get_effective_panodata( $postdata ) : [];
        $show_scene_info = ( $postdata['scene-info-enabled'] ?? 'on' ) !== 'off';
        $tour_layout = is_array( $postdata['tourLayout'] ?? null )
            ? ( $postdata['tourLayout']['layout'] ?? 'default' )
            : ( $postdata['tourLayout'] ?? 'default' );
        $is_classic_layout = 'layout1' !== $tour_layout;

        if (
                ( isset( $_GET['bricks'] ) && wp_unslash( $_GET['bricks'] ) === 'run' ) ||
                ( defined('DOING_AJAX') && DOING_AJAX && isset( $_REQUEST['action'] ) && wp_unslash( $_REQUEST['action'] ) === 'bricks_render_element' ) ||
                ( defined('REST_REQUEST') && REST_REQUEST && isset($_SERVER['REQUEST_URI']) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-json/bricks/v1/render_element') !== false )
        ) {
            return esc_html__('Bricks Editor Mode - WPVR Preview is not available.', 'wpvr');
        }

        do_action('rex_wpvr_embadded_tour', $id);

        $control = false;
        if (isset($postdata['showControls'])) {
            $control = $postdata['showControls'];
        }

        if ($control) {
            if (isset($postdata['customcontrol']) && is_array($postdata['customcontrol'])) {
                $custom_control = $postdata['customcontrol'];
                $gyro_enabled = isset($postdata['gyro']) && in_array($postdata['gyro'], array(true, 1, '1', 'on'), true);
                $custom_control['gyroSwitch'] = $gyro_enabled ? 'on' : 'off';
                $gyro_button_enabled = wpvr_isMobileDevice() && $custom_control['gyroSwitch'] == "on" && $custom_control['gyroscopeSwitch'] == "on";
                if ($custom_control['panupSwitch'] == "on" || $custom_control['panDownSwitch'] == "on" || $custom_control['panLeftSwitch'] == "on" || $custom_control['panRightSwitch'] == "on" || $custom_control['panZoomInSwitch'] == "on" || $custom_control['panZoomOutSwitch'] == "on" || $custom_control['panFullscreenSwitch'] == "on" || $gyro_button_enabled || $custom_control['backToHomeSwitch'] == "on") {
                    $control = false;
                }
            }
        }

        $vrgallery = false;
        if (isset($postdata['vrgallery'])) {
            $vrgallery = $postdata['vrgallery'];
        }

        $vrgallery_title = false;
        if (isset($postdata['vrgallery_title'])) {
            $vrgallery_title = $postdata['vrgallery_title'];
        }

        $vrgallery_display = false;
        if (isset($postdata['vrgallery_display'])) {
            $vrgallery_display = $postdata['vrgallery_display'];
        }
        $vrgallery_icon_size = false;
        if (isset($postdata['vrgallery_icon_size'])) {
            $vrgallery_icon_size = $postdata['vrgallery_icon_size'];
        }
        $gyro = false;
        $gyro_orientation = false;
        if (isset($postdata['gyro'])) {
            $gyro = in_array($postdata['gyro'], array(true, 1, '1', 'on'), true);
            if ($gyro && isset($postdata['deviceorientationcontrol'])) {
                $gyro_orientation = in_array($postdata['deviceorientationcontrol'], array(true, 1, '1', 'on'), true);
            }
        }
        //== Floor plan handle ==//
        $floor_plan_enable = 'off';
        $floor_plan_image = '';
        if (isset($postdata['floor_plan_tour_enabler']) && $postdata['floor_plan_tour_enabler'] == 'on'){
            $floor_plan_enable = $postdata['floor_plan_tour_enabler'];
            if(isset($postdata['floor_plan_attachment_url']) && !empty($postdata['floor_plan_attachment_url'])){
                $floor_plan_image = $postdata['floor_plan_attachment_url'];
            }
        }

        $compass = false;
        $audio_right = "5px";
        if (isset($postdata['compass'])) {
            $compass = $postdata['compass'] == 'on' || $postdata['compass'] != null ? true : false;
            if ($compass) {
                $audio_right = "60px";
            }
        }
        $floor_map_right = "25px";
        if((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')){
            $floor_map_right = "85px";
        }elseif(isset($postdata['compass']) && $postdata['compass'] == 'on'){
            $floor_map_right = "55px";
        }elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
            $floor_map_right = "25px";
        }


        //===explainer  handle===//

        $explainer_right = "10px";
        if ((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on') && ( $floor_plan_enable == 'on' && !empty($floor_plan_image) ) ) {
            $explainer_right = "130px";
        } elseif (isset($postdata['compass']) && $postdata['compass'] == 'on' && ($floor_plan_enable == 'on' && !empty($floor_plan_image) )) {
            $explainer_right = "100px";
        } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on" && ($floor_plan_enable == 'on' && !empty($floor_plan_image) )) {
            $explainer_right = "55px";
        } elseif((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on') ) {
            $explainer_right = "80px";
        }elseif (isset($postdata['compass']) && $postdata['compass'] == 'on') {
            $explainer_right = "55px";
        } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
            $explainer_right = "30px";
        } elseif ($floor_plan_enable == 'on' && !empty($floor_plan_image) ) {
            $explainer_right = "70px";
        }


        $enable_cardboard = '';
        $is_cardboard = get_option('wpvr_cardboard_disable');
        if(wpvr_isMobileDevice() && $is_cardboard == 'true' ){
            $enable_cardboard = 'enable-cardboard';
            $audio_right = "73px";
            if (isset($postdata['compass'])) {
                $compass = $postdata['compass'] == 'on' || $postdata['compass'] != null ? true : false;
                if ($compass) {
                    $audio_right = "130px";
                }
            }
            //===Floor plan  handle===//
            $floor_map_right = "60px";
            if((isset($postdata['compass']) && $postdata['compass'] == 'on') && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')){
                $floor_map_right = "150px";
            }elseif(isset($postdata['compass']) && $postdata['compass'] == 'on'){
                $floor_map_right = "120px";
            }elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
                $floor_map_right = "90px";
            }

            //===explainer  handle===//
            $explainer_right = "65px";

            if ((isset($postdata['compass']) && $postdata['compass'] == true) && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on')) {
                $explainer_right = "150px";
            } elseif((isset($postdata['compass']) && $postdata['compass'] == true) && (isset($postdata['bg_music']) && $postdata['bg_music'] == 'on') && ($floor_plan_enable == 'on' && !empty($floor_plan_image) )) {
                $explainer_right = "180px";
            } elseif (isset($postdata['compass']) && $postdata['compass'] == true && ($floor_plan_enable == 'on' && !empty($floor_plan_image) )) {
                $explainer_right = "150px";
            } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on" && ($floor_plan_enable == 'on' && !empty($floor_plan_image) )) {
                $explainer_right = "120px";
            }elseif (isset($postdata['compass']) && $postdata['compass'] == true) {
                $explainer_right = "130px";
            } elseif (isset($postdata['bg_music']) && $postdata['bg_music'] == "on") {
                $explainer_right = "90px";
            }elseif ($floor_plan_enable == 'on' && !empty($floor_plan_image) ) {
                $explainer_right = "90px";
            }
        }

        //===explainer  handle===//

        $mouseZoom = true;
        if (isset($postdata['mouseZoom'])) {
            if($postdata['mouseZoom'] == "off") {
                $mouseZoom = false;
            }
            else {
                $mouseZoom = true;
            }
        }

        $draggable = true;
        if (isset($postdata['draggable'])) {
            $draggable = ! in_array( $postdata['draggable'], array( 'off', 'false', false ), true );
        }

        $diskeyboard = false;
        if (isset($postdata['diskeyboard'])) {
            $diskeyboard = ! in_array( $postdata['diskeyboard'], array( 'off', 'false', false ), true );
        }

        $keyboardzoom = true;
        if (isset($postdata['keyboardzoom'])) {
            $keyboardzoom = ! in_array( $postdata['keyboardzoom'], array( 'off', 'false', false ), true );
        }

        $autoload = false;

        if (isset($postdata['autoLoad'])) {
            $autoload = in_array( $postdata['autoLoad'], array( true, 1, '1', 'on', 'true' ), true );
        }

        $default_scene = '';
        if (isset($postdata['defaultscene'])) {
            $default_scene = $postdata['defaultscene'];
        }

        $default_global_zoom = '';
        if (isset($postdata['hfov'])) {
            $default_global_zoom = $postdata['hfov'];
        }

        $max_global_zoom = '';
        if (isset($postdata['maxHfov'])) {
            $max_global_zoom = $postdata['maxHfov'];
        }

        $min_global_zoom = '';
        if (isset($postdata['minHfov'])) {
            $min_global_zoom = $postdata['minHfov'];
        }

        $preview = '';
        if (isset($postdata['preview'])) {
            $preview = $postdata['preview'];
        }

        $autorotation = '';
        if (isset($postdata["autoRotate"])) {
            $autorotation = $postdata["autoRotate"];
        }
        $autorotationinactivedelay = '';
        if (isset($postdata["autoRotateInactivityDelay"])) {
            $autorotationinactivedelay = $postdata["autoRotateInactivityDelay"];
        }
        $autorotationstopdelay = '';
        if (isset($postdata["autoRotateStopDelay"])) {
            $autorotationstopdelay = $postdata["autoRotateStopDelay"];
        }

        $scene_fade_duration = '';
        if (isset($postdata['scenefadeduration'])) {
            $scene_fade_duration = $postdata['scenefadeduration'];
        }

        $panodata = array();
        if (isset($postdata['panodata']) && is_array($postdata['panodata'])) {
            $panodata = $postdata['panodata'];
        }

        $hotspoticoncolor = '#00b4ff';
        $hotspotblink = 'on';
        $default_data = array();
        if ($default_global_zoom != '' && $max_global_zoom != '' && $min_global_zoom != '') {
            $default_data = array("firstScene" => $default_scene, "sceneFadeDuration" => $scene_fade_duration, "hfov" => $default_global_zoom, "maxHfov" => $max_global_zoom, "minHfov" => $min_global_zoom);
        } else {
            $default_data = array("firstScene" => $default_scene, "sceneFadeDuration" => $scene_fade_duration);
        }

        $scene_data = array();

        if (!empty($panodata["scene-list"])) {
            foreach ($panodata["scene-list"] as $panoscenes) {
                $scene_ititle = '';
                if (isset($panoscenes["scene-ititle"])) {
                    $scene_ititle = sanitize_text_field($panoscenes["scene-ititle"]);
                }

                $scene_author = '';
                if (isset($panoscenes["scene-author"])) {
                    $scene_author = sanitize_text_field($panoscenes["scene-author"]);
                }

                $scene_author_url = '';
                if (isset($panoscenes["scene-author-url"])) {
                    $scene_author_url = esc_url($panoscenes["scene-author-url"]);
                }

                $scene_vaov = 180;
                if (isset($panoscenes["scene-vaov"])) {
                    $scene_vaov = (float)$panoscenes["scene-vaov"];
                }

                $scene_haov = 360;
                if (isset($panoscenes["scene-haov"])) {
                    $scene_haov = (float)$panoscenes["scene-haov"];
                }


                $scene_vertical_offset = 0;
                if (isset($panoscenes["scene-vertical-offset"])) {
                    $scene_vertical_offset = (float)$panoscenes["scene-vertical-offset"];
                }

                $default_scene_pitch = null;
                if (isset($panoscenes["scene-pitch"])) {
                    $default_scene_pitch = (float)$panoscenes["scene-pitch"];
                }

                $default_scene_yaw = null;
                if (isset($panoscenes["scene-yaw"])) {
                    $default_scene_yaw = (float)$panoscenes["scene-yaw"];
                }

                $scene_max_pitch = '';
                if (isset($panoscenes["scene-maxpitch"])) {
                    $scene_max_pitch = (float)$panoscenes["scene-maxpitch"];
                }


                $scene_min_pitch = '';
                if (isset($panoscenes["scene-minpitch"])) {
                    $scene_min_pitch = (float)$panoscenes["scene-minpitch"];
                }


                $scene_max_yaw = '';
                if (isset($panoscenes["scene-maxyaw"])) {
                    $scene_max_yaw = (float)$panoscenes["scene-maxyaw"];
                }


                $scene_min_yaw = '';
                if (isset($panoscenes["scene-minyaw"])) {
                    $scene_min_yaw = (float)$panoscenes["scene-minyaw"];
                }

                $default_zoom = 100;
                if (isset($panoscenes["scene-zoom"]) && $panoscenes["scene-zoom"] != "") {
                    $default_zoom = $panoscenes["scene-zoom"];
                } else {
                    if ($default_global_zoom != '') {
                        $default_zoom =  (int)$default_global_zoom;
                    }
                }


                $max_zoom = 120;
                if (isset($panoscenes["scene-maxzoom"]) && $panoscenes["scene-maxzoom"] != '') {
                    $max_zoom = (int)$panoscenes["scene-maxzoom"];
                } else {
                    if ($max_global_zoom != '') {
                        $max_zoom =  (int)$max_global_zoom;
                    }
                }



                $min_zoom = 50;
                if (isset($panoscenes["scene-minzoom"]) && $panoscenes["scene-minzoom"] != '') {
                    $min_zoom = (int)$panoscenes["scene-minzoom"];
                } else {
                    if ($min_global_zoom != '') {
                        $min_zoom =  (int)$min_global_zoom;
                    }
                }


                $hotspot_datas = array();
                if (isset($panoscenes["hotspot-list"])) {
                    $hotspot_datas = $panoscenes["hotspot-list"];
                }

                $hotspots = array();


                foreach ($hotspot_datas as $hotspot_data) {
                    $status  = get_option('wpvr_edd_license_status');
                    if ($status !== false && $status == 'valid') {
                        if (!empty($hotspot_data["hotspot-customclass-pro"]) && $hotspot_data["hotspot-customclass-pro"] != 'none') {
                            $hotspot_data['hotspot-customclass'] = $hotspot_data["hotspot-customclass-pro"] . ' custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot_data['hotspot-title']);
                        }
                        if (isset($hotspot_data['hotspot-blink'])) {
                            $hotspotblink = $hotspot_data['hotspot-blink'];
                        }
                    }
                    $has_hotspot_scene_pitch = isset($hotspot_data["hotspot-scene-pitch"])
                        && $hotspot_data["hotspot-scene-pitch"] !== '';
                    $has_hotspot_scene_yaw = isset($hotspot_data["hotspot-scene-yaw"])
                        && $hotspot_data["hotspot-scene-yaw"] !== '';

                    $hotspot_type = $hotspot_data["hotspot-type"] !== 'scene' ? 'info' : $hotspot_data["hotspot-type"];
                    $hotspot_content = '';

                    ob_start();
                    do_action('wpvr_hotspot_content', $hotspot_data);
                    $hotspot_content = ob_get_clean();

                    $is_fluent_form = isset($hotspot_data["hotspot-type"]) && 'fluent_form' === $hotspot_data["hotspot-type"];

                    if ($is_fluent_form) {
                        if (!empty($hotspot_content)) {
                            $hotspot_content = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $hotspot_content);
                        }
                    } else {
                        if (!$hotspot_content) {
                            $hotspot_content = $hotspot_data["hotspot-content"] ?? '';
                        }
                    }

                    $hotspot_url = $this->normalize_hotspot_external_url($hotspot_data["hotspot-url"] ?? '');

                    if (isset($hotspot_data["hotspot-url-open"])) {
                        // New UI: "on" means new tab; legacy renderer: "on" means same tab.
                        $wpvr_url_open = $hotspot_data["hotspot-url-open"] === 'on' ? 'off' : 'on';
                    } elseif (isset($hotspot_data["wpvr_url_open"][0])) {
                        $wpvr_url_open = $hotspot_data["wpvr_url_open"][0];
                    } else {
                        $wpvr_url_open = "off";
                    }
                    $on_hover_content = preg_replace_callback(
                        '/<p>\s*(<img[^>]*>)\s*<br>\s*<\/p>/i',
                        function ($matches) {
                            return $matches[1];
                        },
                        $hotspot_data['hotspot-hover'] ?? ''
                    );


                    $on_hover_content = $this->sanitize_content_preserve_styles($on_hover_content ?? '', false);
                    $on_click_content = preg_replace_callback('/<img[^>]*>/', "replace_callback", $hotspot_content ?? '');
                    $on_click_content = $this->sanitize_content_preserve_styles($on_click_content ?? '', $is_fluent_form);

                    // Shape is independent of the optional custom icon in the
                    // new editor, so it must always be included in the public
                    // hotspot configuration.
                    $hotspot_shape = isset( $hotspot_data['hotspot-shape'] )
                        && in_array( $hotspot_data['hotspot-shape'], [ 'round', 'square', 'hexagon' ], true )
                        ? $hotspot_data['hotspot-shape']
                        : 'round';
                    $hotspot_data_for_on_click=[];
                    if( ('info' === $hotspot_type || 'fluent_form' === $hotspot_data["hotspot-type"]) && !empty($on_click_content)){
                        $hotspot_data_for_on_click = [
                                'on_click_content' => $on_click_content,
                                'tour_id' => $id,
                                'scene_id' => $panoscenes['scene-id'],
                                'hotspot_id' => sanitize_html_class($hotspot_data['hotspot-title']),
                            ];
                    } elseif('info' === $hotspot_type && !empty($hotspot_url)){
                        $hotspot_data_for_on_click = [
                            'on_click_content' => '',
                            'tour_id' => $id,
                            'scene_id' => $panoscenes['scene-id'],
                            'hotspot_id' => sanitize_html_class($hotspot_data['hotspot-title']),
                        ];
                    }

                    $hotspot_info = array(
                        "text" => esc_html(sanitize_text_field($hotspot_data["hotspot-title"])),
                        "pitch" => $hotspot_data["hotspot-pitch"],
                        "yaw" => $hotspot_data["hotspot-yaw"],
                        "type" => $hotspot_type,
                        "cssClass" => $hotspot_data["hotspot-customclass"],
                        "URL" => $hotspot_url,
                        "wpvr_url_open" => $wpvr_url_open,
                        "clickHandlerArgs" => $hotspot_data_for_on_click,
                        'createTooltipArgs' => !empty(trim($on_hover_content)) ? trim($on_hover_content) : '',
                        "sceneId" => $hotspot_data["hotspot-scene"],
                        'hotspot_type' => $hotspot_data['hotspot-type'],
                        'hotspot_target' => 'notBlank',
                        'hotspot_shape' => $hotspot_shape,
                    );

                    // An omitted entry angle lets Pannellum use the target
                    // scene's own default face. Keep explicit zero values.
                    if (!empty($hotspot_data["hotspot-scene"])) {
                        if ($has_hotspot_scene_pitch) {
                            $hotspot_info['targetPitch'] = (float)$hotspot_data["hotspot-scene-pitch"];
                        }
                        if ($has_hotspot_scene_yaw) {
                            $hotspot_info['targetYaw'] = (float)$hotspot_data["hotspot-scene-yaw"];
                        }
                    }
                    $hotspot_info['URL'] = ($hotspot_data['hotspot-type'] === 'fluent_form' || $hotspot_data['hotspot-type'] === 'wc_product') ? '' : $hotspot_info['URL'];

                    if ($hotspot_data["hotspot-customclass"] == 'none' || $hotspot_data["hotspot-customclass"] == '') {
                        unset($hotspot_info["cssClass"]);
                    }
                    array_push($hotspots, $hotspot_info);
                }

                $device_scene = $panoscenes['scene-attachment-url'];
                $mobile_media_resize = get_option('mobile_media_resize');
                $file_accessible = ini_get('allow_url_fopen');
                if ($mobile_media_resize == "true" && $device_scene ) {
                    if ($file_accessible == "1") {
                        $image_info = getimagesize($device_scene);
                        if ( isset($image_info[0]) && $image_info[0] > 4096) {
                            $src_to_id_for_mobile = '';
                            $src_to_id_for_desktop = '';
                            if (wpvr_isMobileDevice()) {
                                $src_to_id_for_mobile = attachment_url_to_postid($panoscenes['scene-attachment-url']);
                                if ($src_to_id_for_mobile) {
                                    $mobile_scene = wp_get_attachment_image_src($src_to_id_for_mobile, 'wpvr_mobile');
                                    if ($mobile_scene[3]) {
                                        $device_scene = $mobile_scene[0];
                                    }
                                }
                            } else {
                                $src_to_id_for_desktop = attachment_url_to_postid($panoscenes['scene-attachment-url']);
                                if ($src_to_id_for_desktop) {
                                    $desktop_scene = wp_get_attachment_image_src($src_to_id_for_mobile, 'full');
                                    if (isset($desktop_scene[0])) {
                                        $device_scene = $desktop_scene[0];
                                    }
                                }
                            }
                        }
                    }
                }

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

                    $scene_info = array("type" => $panoscenes["scene-type"], "cubeMap" => $pano_attachment, "pitch" => $default_scene_pitch, "maxPitch" => $scene_max_pitch, "minPitch" => $scene_min_pitch, "maxYaw" => $scene_max_yaw, "minYaw" => $scene_min_yaw, "yaw" => $default_scene_yaw, "hfov" => $default_zoom, "maxHfov" => $max_zoom, "minHfov" => $min_zoom, "title" => $scene_ititle, "author" => $scene_author, "authorURL" => $scene_author_url, "vaov" => $scene_vaov, "haov" => $scene_haov, "vOffset" => $scene_vertical_offset, "hotSpots" => $hotspots);
                } else {
                    $scene_info = array("type" => $panoscenes["scene-type"], "panorama" => $device_scene, "pitch" => $default_scene_pitch, "maxPitch" => $scene_max_pitch, "minPitch" => $scene_min_pitch, "maxYaw" => $scene_max_yaw, "minYaw" => $scene_min_yaw, "yaw" => $default_scene_yaw, "hfov" => $default_zoom, "maxHfov" => $max_zoom, "minHfov" => $min_zoom, "title" => $scene_ititle, "author" => $scene_author, "authorURL" => $scene_author_url, "vaov" => $scene_vaov, "haov" => $scene_haov, "vOffset" => $scene_vertical_offset, "hotSpots" => $hotspots);
                }


                if (isset($panoscenes["ptyscene"])) {
                    if ($panoscenes["ptyscene"] == "off") {
                        unset($scene_info['pitch']);
                        unset($scene_info['yaw']);
                    }
                }

                if (!$show_scene_info || empty($panoscenes["scene-ititle"])) {
                    unset($scene_info['title']);
                }
                if (!$show_scene_info || empty($panoscenes["scene-author"])) {
                    unset($scene_info['author']);
                }
                if (!$show_scene_info || empty($panoscenes["scene-author-url"])) {
                    unset($scene_info['authorURL']);
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

                // if (isset($panoscenes["czscene"])) {
                //     if ($panoscenes["czscene"] == "off") {
                //         unset($scene_info['hfov']);
                //         unset($scene_info['maxHfov']);
                //         unset($scene_info['minHfov']);
                //     }
                // }

                $scene_array = array();
                $scene_array = array(
                    $panoscenes["scene-id"] => $scene_info
                );
                $scene_data[$panoscenes["scene-id"]] = $scene_info;
            }
        }

        $pano_id_array = array();
        $pano_id_array = array("panoid" => $panoid);
        $pano_response = array();
        $pano_response = array("autoLoad" => $autoload, "showControls" => $control, "orientationSupport" => 'false', "compass" => $compass, 'orientationOnByDefault' => $gyro_orientation, "mouseZoom" => $mouseZoom, "draggable" => $draggable, 'disableKeyboardCtrl' => $diskeyboard, 'keyboardZoom' => $keyboardzoom, "preview" => $preview, "autoRotate" => $autorotation, "autoRotateInactivityDelay" => $autorotationinactivedelay, "autoRotateStopDelay" => $autorotationstopdelay, "default" => $default_data, "scenes" => $scene_data);
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


        if (empty($width)) {
            $width = '600px';
        }
        if (empty($height)) {
            $height = '400px';
        }
        $foreground_color = '#fff';
        $pulse_color = wpvr_hex2rgb($hotspoticoncolor);
        $rgb = wpvr_HTMLToRGB($hotspoticoncolor);
        $hsl = wpvr_RGBToHSL($rgb);
        if ($hsl->lightness > 200) {
            $foreground_color = '#000000';
        } else {
            $foreground_color = '#fff';
        }
        $html = '';

        $html .= '<style>';
        if ($width == 'embed') {
            $html .= 'body{
                overflow: hidden;
           }';
        }
        $status  = get_option('wpvr_edd_license_status');
        if ($status !== false && $status == 'valid') {
            if(isset($postdata['customcss_enable']) && $postdata['customcss_enable'] == 'on'){
                $html .= isset($postdata['customcss']) ? $postdata['customcss'] : '';
            }
        }
        $pano_suffix = (strpos($panoid, 'pano') === 0) ? substr($panoid, 4) : (string) $panoid;
        $panoid2 = 'pano2' . $pano_suffix;
        $master_container_id = ($panoid === 'pano' . $id) ? 'master-container' : ('master-container-' . $pano_suffix);
        $status  = get_option('wpvr_edd_license_status');
        if ($status !== false && $status == 'valid' && ! empty( $panodata['scene-list'] ) && is_array( $panodata['scene-list'] )) {
            foreach ($panodata['scene-list'] as $panoscenes){
                if ( empty( $panoscenes['hotspot-list'] ) || ! is_array( $panoscenes['hotspot-list'] ) ) {
                    continue;
                }
                foreach($panoscenes['hotspot-list'] as $hotspot){
                    if (isset($hotspot['hotspot-customclass-color-icon-value']) && !empty($hotspot['hotspot-customclass-color-icon-value'])) {
                        $hotspoticoncolor = $hotspot['hotspot-customclass-color-icon-value'];
                    } else {
                        $hotspoticoncolor = "#00b4ff";
                    }
                    $hotspot_border = '';
                    if(isset($hotspot['hotspot-border']) && $hotspot['hotspot-border'] == 'on'){
                        $border_width = $hotspot['hotspot-border-width'];
                        $border_color = $hotspot['hotspot-border-color'];
                        $border_style = $hotspot['hotspot-border-style'];
                        $hotspot_border = 'border: '.$border_width.'px '.$border_style.' '.$border_color.';';
                    }
                    $hotspot_background_color = 'background-color: ' . $hotspoticoncolor . ';';
                    $hotspot_animation = ' animation: icon-pulse' . $panoid . '-' . $panoscenes['scene-id'] . '-' . sanitize_html_class($hotspot['hotspot-title']) . ' 1.5s infinite cubic-bezier(.25, 0, 0, 1);
                              '. $hotspot_border.'';
                    $pulse_color = wpvr_hex2rgb($hotspoticoncolor);
                    if (!empty($hotspot["hotspot-customclass-pro"]) && $hotspot["hotspot-customclass-pro"] != 'none') {
                        $border_radius = ' border-radius: 100%;';
                        $hotspot_shape = isset($hotspot["hotspot-shape"]) ? $hotspot["hotspot-shape"] : 'round';
                        if($hotspot_shape === 'square'){
                            $border_radius = '';
                        }
                        if ($hotspot_shape === 'hexagon') {
                            $border_radius = '';
                            $hotspot_background_color = 'background-color: transparent;';
                            $hotspot_animation = '';
                        }

                        if(isset($hotspot['hotspot-custom-icon-color-value']) && !empty($hotspot['hotspot-custom-icon-color-value'])){
                            $foreground_color = $hotspot['hotspot-custom-icon-color-value'];
                        }

                        $html .= '#' . $panoid . ' div.pnlm-hotspot-base.fas.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                          #' . $panoid . ' div.pnlm-hotspot-base.fab.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                          #' . $panoid . ' div.pnlm-hotspot-base.fa-solid.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                          #' . $panoid . ' div.pnlm-hotspot-base.fa.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                          #' . $panoid . ' div.pnlm-hotspot-base.far.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).' {
                              display: block !important;
                             '.$hotspot_background_color.'
                              color: ' . $foreground_color . ';
                              '.$border_radius.'
                              width: 30px;
                              height: 30px;
                              font-size: 16px;
                              line-height: 30px;
                             '.$hotspot_animation.'
                          }';

                        if($hotspot_shape === 'hexagon'){

                            $html .= '#' . $panoid . ' .custom-' . $id . '-' . $panoscenes['scene-id'] . '-' . sanitize_html_class($hotspot['hotspot-title']) . ' .hexagon-wrapper svg path {
                                    fill: ' . $hotspoticoncolor . ';
                                 }';

                            $html .= '#' . $panoid . ' .custom-' . $id . '-' . $panoscenes['scene-id'] . '-' . sanitize_html_class($hotspot['hotspot-title']) . '.pnlm-tooltip:after{
                                    content: "";
                                    position: absolute;
                                    left: 50%;
                                    top: 50%;
                                    transform: translate(-50%, -50%);
                                    width: 85%;
                                    height: 85%;
                                    animation: icon-pulse' . $panoid . '-' . $panoscenes['scene-id'] . '-' . sanitize_html_class($hotspot['hotspot-title']) . ' 1.5s infinite cubic-bezier(.25, 0, 0, 1);
                                    border-radius: 100%;
                                    z-index: -2;
                                 }';

                        }

                        $html .= '#' . $panoid2 . ' div.pnlm-hotspot-base.fas.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                              #' . $panoid2 . ' div.pnlm-hotspot-base.fab.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                              #' . $panoid2 . ' div.pnlm-hotspot-base.fa-solid.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                              #' . $panoid2 . ' div.pnlm-hotspot-base.fa.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).',
                              #' . $panoid2 . ' div.pnlm-hotspot-base.far.custom-' . $id.'-' . $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']).' {
                              display: block !important;
                             '.$hotspot_background_color.'
                              color: ' . $foreground_color . ';
                              '.$border_radius.'
                              width: 30px;
                              height: 30px;
                              font-size: 16px;
                              line-height: 30px;
                              '.$hotspot_animation.'
                      }';
                    }
                    if (isset($hotspot['hotspot-blink'])) {
                        $hotspotblink = $hotspot['hotspot-blink'];
                        if ($hotspotblink == 'on') {
                            $html .= '@-webkit-keyframes icon-pulse'  .$panoid .'-'. $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']) .' {
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
                            $html .= '@-webkit-keyframes icon-pulse'  .$panoid2 .'-'. $panoscenes['scene-id'] .'-'. sanitize_html_class($hotspot['hotspot-title']) .' {
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
                        }
                    }

                }

            }

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
        $floor_plan_custom_color = isset($postdata['floor_plan_custom_color']) ? $postdata['floor_plan_custom_color'] : '#cca92c';
        $foreground_color_pointer = '#fff';
        if($floor_plan_custom_color != ''){
            $pointer_pulse = wpvr_hex2rgb($floor_plan_custom_color);
            $floor_rgb = wpvr_HTMLToRGB($floor_plan_custom_color);
            $floor_hsl = wpvr_RGBToHSL($floor_rgb);
            if ($floor_hsl->lightness > 200) {
                $foreground_color_pointer = '#000000';
            }
            $html .= '
            .wpvr-floor-map .floor-plan-pointer.add-pulse:before {
                border: 17px solid '.$floor_plan_custom_color.';
            }
            @-webkit-keyframes pulse {
                0% {
                    -webkit-box-shadow: 0 0 0 0 rgba('.$pointer_pulse[0].', 0.7);
                }
                70% {
                    -webkit-box-shadow: 0 0 0 10px rgba('.$pointer_pulse[0].', 0);
                }
                100% {
                    -webkit-box-shadow: 0 0 0 0 rgba('.$pointer_pulse[0].', 0);
                }
            }
            @keyframes pulse {
            0% {
                -moz-box-shadow: 0 0 0 0 rgba('.$pointer_pulse[0].', 0.7);
                box-shadow: 0 0 0 0 rgba('.$pointer_pulse[0].', 0.7);
            }
            70% {
                -moz-box-shadow: 0 0 0 10px rgba('.$pointer_pulse[0].', 0);
                box-shadow: 0 0 0 10px rgba('.$pointer_pulse[0].', 0);
            }
            100% {
                -moz-box-shadow: 0 0 0 0 rgba('.$pointer_pulse[0].', 0);
                box-shadow: 0 0 0 0 rgba('.$pointer_pulse[0].', 0);
            }
        }';
        }

        $html .= '</style>';


        $scene_animation = isset($postdata['sceneAnimation']) ? $postdata['sceneAnimation'] : 'off';
        $scene_animation_enabled = in_array( $scene_animation, array( 'on', '1', 1, true ), true );

        if( $scene_animation_enabled && is_plugin_active( 'wpvr-pro/wpvr-pro.php' ) ) {
            $animation_type = isset($postdata['sceneAnimationName']) ? $postdata['sceneAnimationName'] : 'none';
            $animation_type = $animation_type === 'fade' ? 'fade_in' : $animation_type;
            $animationDuration = isset($postdata['sceneAnimationTransitionDuration']) ? $postdata['sceneAnimationTransitionDuration'] : '500ms';
            $animationDelay = isset($postdata['sceneAnimationTransitionDelay']) ? $postdata['sceneAnimationTransitionDelay'] : '0ms';
            $animation_css = apply_filters('wpvr_tour_scene_animation',$animation_type,$animationDuration,$animationDelay,$postdata,$id);
            $html .= $animation_css;
        }

        $container_width         = $width;
        $container_height        = $height;
        $container_mobile_height = $mobile_height;

        if ($width == 'fullwidth') {
            $container_width = "100%";
        } elseif ($width == 'embed') {
            $container_width         = "100%";
            $container_height        = "100%";
            $container_mobile_height = "100%";
        }

        if (wpvr_isMobileDevice()) {
            $html .= '<div id="' . $master_container_id . '" class="wpvr-master-container wpvr-cardboard '.$enable_cardboard.'" style="max-width:' . $container_width . '; width: 100%; height: ' . $container_mobile_height . '; border-radius:' . $radius . '; direction:ltr; ">';
        } else {
            $html .= '<div id="' . $master_container_id . '" class="wpvr-master-container wpvr-cardboard '.$enable_cardboard.'" style="max-width:' . $container_width . '; width: 100%; height: ' . $container_height . '; border-radius:' . $radius . '; direction:ltr; ">';
        }
        $is_pro = apply_filters('is_wpvr_pro_active',false);
        $status  = get_option('wpvr_edd_license_status');
        $is_cardboard = get_option('wpvr_cardboard_disable');
        if ($status !== false &&  'valid' == $status  && $is_pro && wpvr_isMobileDevice() && $is_cardboard == 'true' ) {
            $html .= '<button class="fullscreen-button">';
            $html .= '<span class="expand">';
            $html .= '<i class="fas fa-expand" aria-hidden="true"></i>';
            $html .= '</span>';

            $html .= '<span class="compress">';
            $html .= '<i class="fas fa-minimize" aria-hidden="true"></i>';
            $html .= '</span>';
            $html .= '</button>';
            $embed_mode = '';
            if($width == "embed"){
                $embed_mode = "vr-embade-mode";
            }
            $html .= '<label class="wpvr-cardboard-switcher '.$embed_mode.'">
                <input type="checkbox" class="vr_mode_change' . $pano_suffix . '" name="vr_mode_change" value="off">
                <span class="switcher-box">
                    <span class="normal-mode-tooltip">Normal VR Mode</span>
                    <svg width="78" height="60" viewBox="0 0 78 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M42.25 21.4286C42.25 22.2811 41.9076 23.0986 41.2981 23.7014C40.6886 24.3042 39.862 24.6429 39 24.6429C38.138 24.6429 37.3114 24.3042 36.7019 23.7014C36.0924 23.0986 35.75 22.2811 35.75 21.4286C35.75 20.5761 36.0924 19.7585 36.7019 19.1557C37.3114 18.5529 38.138 18.2143 39 18.2143C39.862 18.2143 40.6886 18.5529 41.2981 19.1557C41.9076 19.7585 42.25 20.5761 42.25 21.4286ZM19.5 30C18.9254 30 18.3743 30.2258 17.9679 30.6276C17.5616 31.0295 17.3333 31.5745 17.3333 32.1429C17.3333 32.7112 17.5616 33.2562 17.9679 33.6581C18.3743 34.06 18.9254 34.2857 19.5 34.2857H28.1667C28.7413 34.2857 29.2924 34.06 29.6987 33.6581C30.1051 33.2562 30.3333 32.7112 30.3333 32.1429C30.3333 31.5745 30.1051 31.0295 29.6987 30.6276C29.2924 30.2258 28.7413 30 28.1667 30H19.5ZM47.6667 32.1429C47.6667 31.5745 47.8949 31.0295 48.3013 30.6276C48.7076 30.2258 49.2587 30 49.8333 30H58.5C59.0746 30 59.6257 30.2258 60.0321 30.6276C60.4384 31.0295 60.6667 31.5745 60.6667 32.1429C60.6667 32.7112 60.4384 33.2562 60.0321 33.6581C59.6257 34.06 59.0746 34.2857 58.5 34.2857H49.8333C49.2587 34.2857 48.7076 34.06 48.3013 33.6581C47.8949 33.2562 47.6667 32.7112 47.6667 32.1429ZM32.5 0C31.9254 0 31.3743 0.225765 30.9679 0.627629C30.5616 1.02949 30.3333 1.57454 30.3333 2.14286V8.57143H18.4167C14.8693 8.57183 11.4528 9.89617 8.84994 12.2798C6.24706 14.6634 4.64954 17.9306 4.37667 21.4286H2.16667C1.59203 21.4286 1.04093 21.6543 0.634602 22.0562C0.228273 22.4581 0 23.0031 0 23.5714V36.4286C0 36.9969 0.228273 37.5419 0.634602 37.9438C1.04093 38.3457 1.59203 38.5714 2.16667 38.5714H4.33333V46.0714C4.33333 49.7655 5.81711 53.3083 8.45825 55.9204C11.0994 58.5325 14.6815 60 18.4167 60H25.3933C29.1269 59.9986 32.7071 58.5311 35.347 55.92L37.921 53.3786C38.0618 53.2393 38.229 53.1288 38.4131 53.0534C38.5971 52.978 38.7943 52.9392 38.9935 52.9392C39.1927 52.9392 39.3899 52.978 39.5739 53.0534C39.758 53.1288 39.9252 53.2393 40.066 53.3786L42.6357 55.92C45.2766 58.5322 48.8586 59.9998 52.5937 60H59.5833C63.3185 60 66.9006 58.5325 69.5418 55.9204C72.1829 53.3083 73.6667 49.7655 73.6667 46.0714V38.5714H75.8333C76.408 38.5714 76.9591 38.3457 77.3654 37.9438C77.7717 37.5419 78 36.9969 78 36.4286V23.5714C78 23.0031 77.7717 22.4581 77.3654 22.0562C76.9591 21.6543 76.408 21.4286 75.8333 21.4286H73.6233C73.3505 17.9306 71.753 14.6634 69.1501 12.2798C66.5472 9.89617 63.1307 8.57183 59.5833 8.57143H47.6667V2.14286C47.6667 1.57454 47.4384 1.02949 47.0321 0.627629C46.6257 0.225765 46.0746 0 45.5 0H32.5ZM69.3333 22.5V46.0714C69.3333 48.6289 68.3061 51.0816 66.4776 52.89C64.6491 54.6983 62.1692 55.7143 59.5833 55.7143H52.5937C50.0093 55.7132 47.5311 54.6973 45.7037 52.89L43.1297 50.3486C42.5864 49.8108 41.9413 49.3842 41.2312 49.0931C40.5211 48.8021 39.76 48.6522 38.9913 48.6522C38.2227 48.6522 37.4616 48.8021 36.7515 49.0931C36.0414 49.3842 35.3963 49.8108 34.853 50.3486L32.2833 52.89C30.4559 54.6973 27.9777 55.7132 25.3933 55.7143H18.4167C15.8308 55.7143 13.3509 54.6983 11.5224 52.89C9.6939 51.0816 8.66667 48.6289 8.66667 46.0714V22.5C8.66667 19.9426 9.6939 17.4899 11.5224 15.6815C13.3509 13.8731 15.8308 12.8571 18.4167 12.8571H59.5833C62.1692 12.8571 64.6491 13.8731 66.4776 15.6815C68.3061 17.4899 69.3333 19.9426 69.3333 22.5Z" fill="#216DF0"/>
                    </svg>
                </span>
            </label>';

        }

        if ($width == 'fullwidth') {
            if (wpvr_isMobileDevice()) {
                $html .= '<div class="cardboard-vrfullwidth vrfullwidth">';
                $html .= '<div id="' . $panoid2 . '" class="pano-wrap pano-left cardboard-half pano2' . $id . '" style="width: 49%!important; border-radius:' . $radius . ' text-align:center; direction:ltr;" ><div id="center-pointer2' . $pano_suffix . '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';
                $html .= '<div id="' . $panoid . '" class="pano-wrap pano-right pano' . $id . '" style="width: 100%; text-align:center; direction:ltr; border-radius:' . $radius . '" >';
            } else {
                $html .= '<div id="' . $panoid2 . '" class="pano-wrap pano-left pano2' . $id . '" style="width: 49%; border-radius:' . $radius . '"><div id="center-pointer2' . $pano_suffix . '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';
                if ($radius) {
                    $html .= '<div id="' . $panoid . '" class="pano-wrap vrfullwidth pano' . $id . '" style=" text-align:center; height: ' . $height . '; border-radius:' . $radius . '; direction:ltr;" >';
                } else {
                    $html .= '<div id="' . $panoid . '" class="pano-wrap vrfullwidth pano' . $id . '" style=" text-align:center; height: ' . $height . '; direction:ltr;" >';
                }
            }
        } elseif ($width == 'embed') {
            $html .= '<div class="cardboard-vrembed vrembed">';
            $html .= '<div id="' . $panoid2 . '" class="pano-wrap pano-left pano2' . $id . '" style=" width: 49%!important; text-align:center; direction:ltr;" ><div id="center-pointer2' . $pano_suffix . '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';
            $html .= '<div id="' . $panoid . '" class="pano-wrap pano-right pano' . $id . '" style="width: 100%; height: 100%; text-align:center; direction:ltr;" >';
        } else {
            if (wpvr_isMobileDevice()) {
                $html .= '<div id="' . $panoid2 . '" class="pano-wrap pano-left cardboard-half pano2' . $id . '" style="width: 49%; border-radius:' . $radius . '"><div id="center-pointer2' . $pano_suffix . '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';
                if ($radius) {
                    $html .= '<div id="' . $panoid . '" class="pano-wrap pano-right pano' . $id . '" style=" width: 100%; border-radius:' . $radius . ';">';
                } else {
                    $html .= '<div id="' . $panoid . '" class="pano-wrap pano-right pano' . $id . '" style=" width: 100%; ">';
                }
            } else {
                $html .= '<div id="' . $panoid2 . '" class="pano-wrap pano-left pano2' . $id . '" style="width: 49%; border-radius:' . $radius . '"><div id="center-pointer2' . $pano_suffix . '" class="vr-pointer-container"><span class="center-pointer"></span></div></div>';

                if ($radius) {
                    $html .= '<div id="' . $panoid . '" class="pano-wrap pano-right pano' . $id . '" style="width: 100%; border-radius:' . $radius . ';">';
                } else {
                    $html .= '<div id="' . $panoid . '" class="pano-wrap pano-right pano' . $id . '" style="width: 100%;">';
                }
            }
        }
        // Vr mode transction scene to scene
        if ($status !== false &&  'valid' == $status  && $is_pro) {
            $html .= '<div id="center-pointer' . $pano_suffix . '" class="vr-pointer-container" style="display:none"><span class="center-pointer"></span></div>';
        }
        $social_logo_top = '';
        //===company logo===//
        if (isset($postdata['cpLogoSwitch'])) {
            $cpLogoImg = $postdata['cpLogoImg'] ?? '';
            $cpLogoContent = $postdata['cpLogoContent'] ?? '';
            if ($postdata['cpLogoSwitch'] == 'on' && 'valid' == $status  && $is_pro) {
                $html .= '<div id="cp-logo-controls">';
                $html .= '<div class="cp-logo-ctrl" id="cp-logo">';
                if ($cpLogoImg) {
                    $social_logo_top = '50px';
                    $html .= '<img loading="lazy" src="' . $cpLogoImg . '" alt="Company Logo">';
                }

                if ($cpLogoContent) {
                    $html .= '<div class="cp-info">' . esc_attr($cpLogoContent) . '</div>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        //===company logo ends===//

        //===Generic Form===//
        if (isset($postdata["genericform"]) && $postdata["genericform"] === 'on' && 'valid' == $status  && $is_pro) {
            // Process shortcode with fallback handling
            $shortcode_content = '';
            if (isset($postdata["genericformshortcode"]) && !empty(trim($postdata["genericformshortcode"]))) {
                $shortcode_content = do_shortcode( $postdata["genericformshortcode"] );
            } else {
                $shortcode_content = '<p class="error-message">No shortcode found.</p>';
            }

            // Generate the form trigger button
            $generic_form_icon = isset($postdata['genericformicon']) ? (string) $postdata['genericformicon'] : 'fab fa-wpforms';
            $generic_form_icon = implode(' ', array_filter(array_map('sanitize_html_class', preg_split('/\s+/', $generic_form_icon))));
            $generic_form_icon = $generic_form_icon ?: 'fab fa-wpforms';
            $generic_form_icon_color = sanitize_hex_color($postdata['genericformiconcolor'] ?? '') ?: '#f7fffb';
            $html .= '<div class="generic_form_button" id="generic_form_button_' . esc_attr($pano_suffix) . '">';
            $html .= '<div class="generic-form-icon" title ="Generic Form" id="generic_form_target_' . esc_attr($pano_suffix) . '"><i class="' . esc_attr($generic_form_icon) . '" style="color:' . esc_attr($generic_form_icon_color) . ';"></i></div>';
            $html .= '</div>';

            // Generate the modal form container
            $html .= '<div class="wpvr-generic-form" id="wpvr-generic-form' . esc_attr($pano_suffix) . '" style="display: none">';
            $html .= '<span class="close-generic-form"><i class="fa fa-times"></i></span>';
            $html .= '<div class="generic-form-container">' . $shortcode_content . '</div>';
            $html .= '</div>';
        }
        //===Generic Form ends===//

        //===Background Tour===//
        if (isset($postdata['bg_tour_enabler'])) {

            $bg_tour_enabler = $postdata['bg_tour_enabler'];
            if ($bg_tour_enabler == 'on') {
                $bg_tour_navmenu = $postdata['bg_tour_navmenu'] ?? 'off';
                $bg_tour_title = $postdata['bg_tour_title'] ?? '';
                $bg_tour_subtitle = $postdata['bg_tour_subtitle'] ?? '';

                if ($bg_tour_navmenu == 'on') {
                    $menuLocations = get_nav_menu_locations();
                    if (!empty($menuLocations['primary'])) {
                        $menuID = $menuLocations['primary'];
                        $primaryNav = wp_get_nav_menu_items($menuID);
                        $html .= '<ul class="wpvr-navbar-container">';
                        foreach ($primaryNav as $primaryNav_key => $primaryNav_value) {
                            if ($primaryNav_value->menu_item_parent == "0") {
                                $html .= '<li>';
                                $html .= '<a href="' . $primaryNav_value->url . '">' . $primaryNav_value->title . '</a>';
                                $html .= '<ul class="wpvr-navbar-dropdown">';
                                foreach ($primaryNav as $pm_key => $pm_value) {
                                    if ($pm_value->menu_item_parent == $primaryNav_value->ID) {
                                        $html .= '<li>';
                                        $html .= '<a href="' . $pm_value->url . '">' . $pm_value->title . '</a>';
                                        $html .= '</li>';
                                    }
                                }
                                $html .= '</ul>';
                                $html .= '</li>';
                            }
                        }
                        $html .= '</ul>';
                    }
                }
                if($is_pro && 'valid' == $status ){
                    $html .= '<div class="wpvr-home-content">';
                    $html .= '<div class="wpvr-home-title">' . $bg_tour_title . '</div>';
                    $html .= '<div class="wpvr-home-subtitle">' . $bg_tour_subtitle . '</div>';
                    $html .= '</div>';
                }
            }
        }
        //===Background Tour End===//

        //===Custom Control===//
        if (isset($custom_control)) {
            $gyro_button_enabled = wpvr_isMobileDevice() && $custom_control['gyroSwitch'] == "on" && $custom_control['gyroscopeSwitch'] == "on";
            if ($custom_control['panZoomInSwitch'] == "on" || $custom_control['panZoomOutSwitch'] == "on" || $gyro_button_enabled || $custom_control['backToHomeSwitch'] == "on") {
                $html .= '<div id="zoom-in-out-controls' . $pano_suffix . '" class="zoom-in-out-controls">';

                if ($custom_control['backToHomeSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                    $html .= '<div class="ctrl" id="backToHome' . $pano_suffix . '"><i class="' . $custom_control['backToHomeIcon'] . '" style="color:' . $custom_control['backToHomeColor'] . ';"></i></div>';
                }

                if ($custom_control['panZoomInSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                    $html .= '<div class="ctrl" id="zoom-in' . $pano_suffix . '"><i class="' . $custom_control['panZoomInIcon'] . '" style="color:' . $custom_control['panZoomInColor'] . ';"></i></div>';
                }

                if ($custom_control['panZoomOutSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                    $html .= '<div class="ctrl" id="zoom-out' . $pano_suffix . '"><i class="' . $custom_control['panZoomOutIcon'] . '" style="color:' . $custom_control['panZoomOutColor'] . ';"></i></div>';
                }
                if ($gyro_button_enabled && 'valid' == $status  && $is_pro) {
                    $html .= '<div class="ctrl" id="gyroscope' . $pano_suffix . '" ><i class="' . $custom_control['gyroscopeIcon'] . '" style="color:' . $custom_control['gyroscopeColor'] . ';"></i></div>';
                }
                $html .= '</div>';
            }
            //===zoom in out Control===//

            if (($custom_control['panupSwitch'] == "on" || $custom_control['panDownSwitch'] == "on" || $custom_control['panLeftSwitch'] == "on" || $custom_control['panRightSwitch'] == "on" || $custom_control['panFullscreenSwitch'] == "on" ) && 'valid' == $status  && $is_pro) {
                //===Custom Control===//
                $html .= '<div class="controls" id="controls' . $pano_suffix . '">';

                if ($custom_control['panupSwitch'] == "on") {
                    $html .= '<div class="ctrl pan-up" id="pan-up' . $pano_suffix . '"><i class="' . $custom_control['panupIcon'] . '" style="color:' . $custom_control['panupColor'] . ';"></i></div>';
                }

                if ($custom_control['panDownSwitch'] == "on") {
                    $html .= '<div class="ctrl pan-down" id="pan-down' . $pano_suffix . '"><i class="' . $custom_control['panDownIcon'] . '" style="color:' . $custom_control['panDownColor'] . ';"></i></div>';
                }

                if ($custom_control['panLeftSwitch'] == "on") {
                    $html .= '<div class="ctrl pan-left" id="pan-left' . $pano_suffix . '"><i class="' . $custom_control['panLeftIcon'] . '" style="color:' . $custom_control['panLeftColor'] . ';"></i></div>';
                }

                if ($custom_control['panRightSwitch'] == "on") {
                    $html .= '<div class="ctrl pan-right" id="pan-right' . $pano_suffix . '"><i class="' . $custom_control['panRightIcon'] . '" style="color:' . $custom_control['panRightColor'] . ';"></i></div>';
                }

                if ($custom_control['panFullscreenSwitch'] == "on") {
                    $html .= '<div class="ctrl fullscreen" id="fullscreen' . $pano_suffix . '"><i class="' . $custom_control['panFullscreenIcon'] . '" style="color:' . $custom_control['panFullscreenColor'] . ';"></i></div>';
                }
                $html .= '</div>';
            }
        }
        //===Custom Control===//

        //===explainer button===//
        $html .= wpvr_render_explainer_button(
            isset( $custom_control ) ? $custom_control : null,
            $postdata,
            $is_pro,
            $autoload,
            $explainer_right,
            $pano_suffix
        );
        //===explainer button end===//

        //===Floor map button===//
        $status  = get_option('wpvr_edd_license_status');
        $has_floor_plan = ( $status !== false && 'valid' == $status && $is_pro && 'on' == $floor_plan_enable && ! empty( $floor_plan_image ) );
        if ( $has_floor_plan ) {
            $html .= '<div class="floor_map_button" id="floor_map_button_' . $pano_suffix . '" style="right:'.$floor_map_right.'">';
            $html .= '<div class="ctrl" id="floor_map_target_' . $pano_suffix . '"><i class="fas fa-map" style="color:#f7fffb;"></i></div>';
            $html .= '</div>';
        }
        //===floor map button===//

        if ($vrgallery &&  'valid' == $status  && $is_pro ) {
            //===Carousal setup===//
            $size = '';
            if($vrgallery_icon_size){
                $size = 'vrg-icon-size-large';
            }
            $html .= '<div id="vrgcontrols' . $pano_suffix . '" class="vrgcontrols">';

            $html .= '<div class="vrgctrl' . $pano_suffix . ' vrbounce '.$size.'">';
            $html .= '</div>';
            $html .= '</div>';

            $gallery_layout_class = isset($postdata['tourLayout']['layout']) && 'layout1' === $postdata['tourLayout']['layout']
                ? 'wpvr-gallery--modern'
                : 'wpvr-gallery--classic';
            $html .= '<div id="sccontrols' . $pano_suffix . '" class="scene-gallery vrowl-carousel ' . esc_attr($gallery_layout_class) . '">';
            if (isset($panodata["scene-list"])) {
                foreach ($panodata["scene-list"] as $panoscenes) {
                    $scene_key = $panoscenes['scene-id'];

                    if ($vrgallery_title == 'on') {
                        $scene_key_title = isset($panoscenes['scene-ititle']) ? sanitize_text_field($panoscenes['scene-ititle']) : '';
                    } else {
                        $scene_key_title = "";
                    }

                    if ($panoscenes['scene-type'] == 'cubemap') {
                        $img_src_url = $panoscenes['scene-attachment-url-face0'];
                    } else {
                        $img_src_url = $panoscenes['scene-attachment-url'];
                    }

                    $src_to_id = attachment_url_to_postid($img_src_url);
                    $thumbnail_array = wp_get_attachment_image_src($src_to_id, 'thumbnail');
                    if ($thumbnail_array) {
                        $thumbnail = $thumbnail_array[0];
                    } else {
                        $thumbnail = $img_src_url;
                    }

                    if( isset($postdata['tourLayout']['layout']) && 'layout1' !== $postdata['tourLayout']['layout']) {
                        $html .= '<ul><li title="Click to view scene"><span class="scene-title" title="' . esc_attr($scene_key_title) . '">' . $scene_key_title . '</span><img loading="lazy" class="scctrl" id="' . $scene_key . '_gallery_' . $pano_suffix . '" src="' . $thumbnail . '"></li></ul>';
                    }else {
                        $html .= '<ul><li title="Click to view scene"><img loading="lazy" class="scctrl" id="' . $scene_key . '_gallery_' . $pano_suffix . '" src="' . $thumbnail . '"><span class="scene-title" title="' . esc_attr($scene_key_title) . '">' . $scene_key_title . '</span></li></ul>';
                    }
                }
            }

            $html .= '</div>';

            $html .= '
            <div class="wpvr_slider_nav">
            <button type="button" role="presentation" class="wpvr_owl_prev">
                <div class="nav-btn prev-slide"><i class="fa fa-angle-left"></i></div>
            </button>
            <button type="button" role="presentation" class="wpvr_owl_next">
                <div class="nav-btn next-slide"><i class="fa fa-angle-right"></i></div>
            </button>
            </div>
            ';

            //===Carousal setup end===//
        }
        
        //===Call TO  action Button===//
        $bg_music           = isset($postdata['bg_music']) ? $postdata['bg_music'] : 'off';
        $bg_music_url       = isset($postdata['bg_music_url']) ? $postdata['bg_music_url'] : '';
        $autoplay_bg_music  = isset($postdata['autoplay_bg_music']) ? $postdata['autoplay_bg_music'] : 'off';
        $loop_bg_music      = isset($postdata['loop_bg_music']) ? $postdata['loop_bg_music'] : 'off';

        $bg_loop = ($loop_bg_music === 'on') ? 'loop' : '';
        $autoplay_attr = ($autoplay_bg_music === 'on') ? 'autoplay' : '';
        $audio_muted_attr = ($autoplay_bg_music === 'on') ? 'muted' : '';
        $audio_icon_class = 'fa-volume-mute'; // Always start with mute icon

        if ($bg_music === 'on' && 'valid' == $status  && $is_pro) {
            $html .= '<div id="adcontrol' . esc_attr( $pano_suffix ) . '" class="adcontrol" style="right:' . esc_attr( $audio_right ) . '">';
            $html .= '<audio id="vrAudio' . esc_attr($pano_suffix) . '" class="vrAudioDefault" data-autoplay="' . esc_attr($autoplay_bg_music) . '" onended="audionEnd' . esc_attr($pano_suffix) . '()" ' . $autoplay_attr . ' ' . $audio_muted_attr . ' ' . $bg_loop . '>
                        <source src="' . esc_url($bg_music_url) . '" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>';
            $html .= '<button onclick="playPause' . esc_attr($pano_suffix) . '()" class="ctrl audio_control" id="audio_control' . esc_attr($pano_suffix) . '">
                        <i id="vr-volume' . esc_attr($pano_suffix) . '" class="wpvrvolumeicon' . esc_attr($pano_suffix) . ' fas ' . esc_attr($audio_icon_class) . '" style="color:#fff;"></i>
                    </button>';
            $html .= '</div>';
        }
        //===Explainer video section===//
        $explainerContent = "";
        if (isset($postdata['explainerContent'])) {
            $explainerContent = $this->wpvr_sanitize_iframe_only($postdata['explainerContent']);
        }
        $html .= '<div class="explainer" id="explainer' . $pano_suffix . '" style="display: none">';
        $html .= '<span class="close-explainer-video"><i class="fa fa-times"></i></span>';
        $html .= '' . $explainerContent . '';
        $html .= '</div>';
        //===Explainer video section End===//

        //===Scene navigation Control===//
        if (isset($postdata['scene_navigation']) && $postdata['scene_navigation'] === 'on' && 'valid' == $status  && $is_pro) {
            $scene_navigation_nav_class = 'custom-scene-navigation-nav' . ( $is_classic_layout ? ' wpvr-scene-navigation-nav--classic' : '' );
            $html .= '<style>
                #et-boc .et-l .pnlm-controls-container, 
                .pnlm-controls-container{
                    top: 33px;
                }
                
                #et-boc .et-l .zoom-in-out-controls, 
                .zoom-in-out-controls {
                    top: 37px;
                }
            </style>';
            $html .= '<div id="custom-scene-navigation' . $pano_suffix . '" class="custom-scene-navigation">
                <span class="hamburger-menu"><svg width="16" height="10" fill="none" viewBox="0 0 22 15" xmlns="http://www.w3.org/2000/svg"><rect width="21.177" height="2.647" fill="#f7fffb" rx="1.324"/><rect width="21.177" height="2.647" y="6.177" fill="#f7fffb" rx="1.324"/><rect width="21.177" height="2.647" y="12.352" fill="#f7fffb" rx="1.324"/></svg></span> 
              </div>
              
              <div id="custom-scene-navigation-nav' . $pano_suffix . '" class="' . esc_attr( $scene_navigation_nav_class ) . '">
                  <ul></ul>
              </div> 
              ';
        }
        //===Scene navigation  Control===//


        if( 'embed' === $width){
            if(WPVR_Helper::is_enable_social_share($postdata) === 'on'){
                $html .= '<div id="wpvr-social-share-bg-box'.$pano_suffix.'" class="wpvr-social-share-bg-box" style="top:'.$social_logo_top.'">
                            <span class="share-btn-svg"><svg fill="none" viewBox="0 0 24 24" width="24" height="24" ><path fill="#1F1CF4" d="M18.4 2.4a3.2 3.2 0 00-3.2 3.2 3.2 3.2 0 00.075.67L8.01 9.901A3.2 3.2 0 005.6 8.8a3.2 3.2 0 101.325 6.112 3.2 3.2 0 001.086-.812l7.261 3.632a3.2 3.2 0 101.803-2.242c-.415.189-.786.466-1.085.81l-7.261-3.63A3.2 3.2 0 008.8 12a3.2 3.2 0 00-.075-.667L15.991 7.7a3.2 3.2 0 002.41 1.1 3.2 3.2 0 100-6.4z"/></svg></span>
                            <nav class="wpvr-share-nav">
                                '.WPVR_Helper::social_media_share_links_display_in_embed(home_url().'/?embed_page='. $id).'
                            </nav>
                        </div>';
            }
        }
        //===Floor plan section===//
        if ( $has_floor_plan ) {
            $floor_map_image = $floor_plan_image;
            $floor_map_pointer = isset($postdata['floor_plan_pointer_position']) && is_array($postdata['floor_plan_pointer_position']) ? $postdata['floor_plan_pointer_position'] : array();
            $floor_map_scene_id = isset($postdata['floor_plan_data_list']) && is_array($postdata['floor_plan_data_list']) ? $postdata['floor_plan_data_list'] : array();
            $floor_plan_custom_color = isset($postdata['floor_plan_custom_color']) && ! empty($postdata['floor_plan_custom_color']) ? $postdata['floor_plan_custom_color'] : '#cca92c';
            $floor_plan_direction_indicator = isset($postdata['floor_plan_direction_indicator']) ? $postdata['floor_plan_direction_indicator'] : 'on';

            $media_alt     = '';
            $attachment_id = attachment_url_to_postid( $floor_map_image );

            if ( $attachment_id ) {
                $media_alt = sanitize_text_field( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
            }

            if ( ! empty( $media_alt ) ) {
                $floor_map_alt = $media_alt;
            } else {
                $tour_title = sanitize_text_field( get_the_title( $id ) );

                $floor_map_alt = ! empty( $tour_title )
                    /* translators: %s: tour title */
                    ? sprintf( __( '%s - Floor Plan', 'wpvr' ), $tour_title )
                    : __( 'Floor Plan', 'wpvr' );
            }

            $html .= '<div class="wpvr-floor-map" id="wpvr-floor-map' . $pano_suffix . '" style="display: none">';
            $html .= '<span class="close-floor-map-plan"><i class="fa fa-times"></i></span>';
            $html .= '<img loading="lazy" src="' . esc_url( $floor_map_image ) . '" alt="' . esc_attr( $floor_map_alt ) . '">';
            foreach ( $floor_map_pointer as $key => $pointer_position ) {
                $pointer_id = '';
                $data_top   = '';
                $data_left  = '';
                $style      = '';

                if ( is_object( $pointer_position ) ) {
                    $pointer_id = isset( $pointer_position->id ) ? (string) $pointer_position->id : '';
                    $data_top   = isset( $pointer_position->data_top ) ? (string) $pointer_position->data_top : '';
                    $data_left  = isset( $pointer_position->data_left ) ? (string) $pointer_position->data_left : '';
                    $style      = isset( $pointer_position->style ) ? (string) $pointer_position->style : '';
                } elseif ( is_array( $pointer_position ) ) {
                    $pointer_id = isset( $pointer_position['id'] ) ? (string) $pointer_position['id'] : '';
                    $data_top   = isset( $pointer_position['data_top'] ) ? (string) $pointer_position['data_top'] : '';
                    $data_left  = isset( $pointer_position['data_left'] ) ? (string) $pointer_position['data_left'] : '';
                    $style      = isset( $pointer_position['style'] ) ? (string) $pointer_position['style'] : '';
                }

                $scene_id_val = '';
                if ( isset( $floor_map_scene_id[ $key ] ) ) {
                    $scene_item = $floor_map_scene_id[ $key ];
                    if ( is_object( $scene_item ) && isset( $scene_item->value ) ) {
                        $scene_id_val = (string) $scene_item->value;
                    } elseif ( is_array( $scene_item ) && isset( $scene_item['value'] ) ) {
                        $scene_id_val = (string) $scene_item['value'];
                    } elseif ( is_scalar( $scene_item ) ) {
                        $scene_id_val = (string) $scene_item;
                    }
                }

                $html .= '<div class="floor-plan-pointer ui-draggable ui-draggable-handle" scene_id="' . esc_attr( $scene_id_val ) . '" id="' . esc_attr( $pointer_id ) . '" data-top="' . esc_attr( $data_top ) . '" data-left="' . esc_attr( $data_left ) . '" style="' . esc_attr( $style ) . '">                        
                                        <svg class="floor-pointer-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11.5" stroke="' . esc_attr( $floor_plan_custom_color ) . '"/>
                                            <circle cx="12" cy="12" r="5" fill="' . esc_attr( $foreground_color_pointer ) . '"/>
                                        </svg>';

                // Only add the floor pointer flash SVG if floor_plan_direction_indicator is "on"
                if ( $floor_plan_direction_indicator === 'on' ) {
                    $html .= '<svg class="floor-pointer-flash" width="54" height="35" viewBox="0 0 54 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.454054 1.32433L11.7683 34.3243C11.9069 34.7285 12.287 35 12.7143 35H41.2857C41.713 35 42.0931 34.7285 42.2317 34.3243L53.5459 1.32432C53.7685 0.675257 53.2862 0 52.6 0H1.4C0.713843 0 0.231517 0.675258 0.454054 1.32433Z" fill="url(#paint0_linear_1_10)"/>
                                <defs>
                                <linearGradient id="paint0_linear_1_10" x1="27" y1="4.59807e-08" x2="26.5" y2="28" gradientUnits="userSpaceOnUse">
                                <stop stop-color="' . esc_attr( $floor_plan_custom_color ) . '" stop-opacity="0"/>
                                <stop offset="1" stop-color="' . esc_attr( $floor_plan_custom_color ) . '"/>
                                </linearGradient>
                                </defs>
                            </svg>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        //===Floor plan section===//

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

        $html .= '<div class="custom-ifram-wrapper" style="display: none;">';
        $html .= '<i class="fa fa-times cross" data-id="' . $id . '"></i>';

        $html .= '<div class="custom-ifram-flex">';
        $html .= '<div class="custom-ifram">';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        if( "embed" == $width ){
            $html .= '</div>';
        }


        if ($status !== false &&  'valid' == $status  && $is_pro) {
            $call_to_action = isset($postdata['calltoaction']) ? $postdata['calltoaction'] : 'off';
            if( 'on' == $call_to_action){
                $buttontext = isset($postdata['buttontext']) ? $postdata['buttontext'] : '';
                $buttonurl = isset($postdata['buttonurl']) ? $postdata['buttonurl'] : '';
                $cta_btn_style = isset($postdata['button_configuration']) ? $postdata['button_configuration'] : array();

                $button_open_new_tab = isset($cta_btn_style['button_open_new_tab']) ? $cta_btn_style['button_open_new_tab'] : "off";
                $target = '_self';
                $button_position = isset($cta_btn_style['button_position']) ? $cta_btn_style['button_position'] : "";
                $background_color = isset($cta_btn_style['button_background_color']) ? $cta_btn_style['button_background_color'] : "";
                $color = isset($cta_btn_style['button_font_color']) ? $cta_btn_style['button_font_color'] : "";
                $font_size = isset($cta_btn_style['button_font_size']) ? $cta_btn_style['button_font_size'] : "";
                $font_weight = isset($cta_btn_style['button_font_weight']) ? $cta_btn_style['button_font_weight'] : "";
                $text_align = isset($cta_btn_style['button_alignment']) ? $cta_btn_style['button_alignment'] : "";
                $text_transform = isset($cta_btn_style['button_transform']) ? $cta_btn_style['button_transform'] : "";
                $font_style = isset($cta_btn_style['button_text_style']) ? $cta_btn_style['button_text_style'] : "";
                $text_decoration = isset($cta_btn_style['button_text_decoration']) ? $cta_btn_style['button_text_decoration'] : "";
                $line_height = isset($cta_btn_style['button_line_height']) ? $cta_btn_style['button_line_height'] : "";
                $letter_spacing = isset($cta_btn_style['button_letter_spacing']) ? $cta_btn_style['button_letter_spacing'] : "";
                $word_spacing = isset($cta_btn_style['button_word_spacing']) ? $cta_btn_style['button_word_spacing'] : "";

                $border_width = isset($cta_btn_style['button_border_width']) ? $cta_btn_style['button_border_width'] : "";
                $border_style = isset($cta_btn_style['button_border_style']) ? $cta_btn_style['button_border_style'] : "";
                $border_color = isset($cta_btn_style['button_border_color']) ? $cta_btn_style['button_border_color'] : "";
                $border_radius = isset($cta_btn_style['button_border_radius']) ? $cta_btn_style['button_border_radius'] : "";

                $button_pt = isset($cta_btn_style['button_pt']) ? $cta_btn_style['button_pt'] : "";
                $button_pr = isset($cta_btn_style['button_pr']) ? $cta_btn_style['button_pr'] : "";
                $button_pb = isset($cta_btn_style['button_pb']) ? $cta_btn_style['button_pb'] : "";
                $button_pl = isset($cta_btn_style['button_pl']) ? $cta_btn_style['button_pl'] : "";

                if($button_open_new_tab == 'on'){
                    $target = '_blank';
                }
                $style = 'background-color: '.$background_color.';
                          color: '.$color.';
                          font-size: '.$font_size.'px;
                          font-weight: '.$font_weight.';
                          text-align: center;
                          display: inline-block;
                          text-transform: '.$text_transform.';
                          font-style: '.$font_style.';
                          text-decoration: '.$text_decoration.';
                          line-height: '.$line_height.';
                          letter-spacing: '.$letter_spacing.'px;
                          word-spacing: '.$word_spacing.'px;
                          border: '.$border_width.'px '.$border_style.' '.$border_color.';
                          border-radius: '.$border_radius.'px;
                          padding: '.$button_pt.'px '.$button_pr.'px '.$button_pb.'px '.$button_pl.'px;
                         ';
                $cta_width = ( $width === 'fullwidth' || $width === 'embed' ) ? '100%' : $width;
                $html .= '<div class="wpvr-call-to-action-button position-'.$text_align.'" style="max-width:' . $cta_width . '">
                        <a href="'.$buttonurl.'" style="'.$style.'" target="'.$target.'">'.$buttontext.'</a>
                      </div>';

            }
        }


        //script started
        $html .= '<script>';
        if (isset($postdata['bg_music']) && $bg_music == 'on' && 'valid' == $status  && $is_pro) {
            $html .= '
            var x' . $pano_suffix . ' = document.getElementById("vrAudio' . $pano_suffix . '");
            var playing' . $pano_suffix . ' = false;
            var autoplaySupported' . $pano_suffix . ' = false;
            var alertShown' . $pano_suffix . ' = false;
            var autoplayChecked' . $pano_suffix . ' = false;
        
            function playPause' . $pano_suffix . '() {
                if (playing' . $pano_suffix . ') {
                    jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                    x' . $pano_suffix . '.pause();
                    jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "off");
                    playing' . $pano_suffix . ' = false;
                } else {
                    x' . $pano_suffix . '.muted = false;
                    x' . $pano_suffix . '.play().then(function() {
                        jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                        jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "on");
                        playing' . $pano_suffix . ' = true;
                    }).catch(function(e) {
                        console.log("Play failed:", e);
                    });
                }
            }
        
            function audionEnd' . $pano_suffix . '() {
                playing' . $pano_suffix . ' = false;
                jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "off");
            }
        
            x' . $pano_suffix . '.addEventListener("ended", audionEnd' . $pano_suffix . ');';

                    if ($autoplay_bg_music == 'on') {
                        $html .= '
        
                x' . $pano_suffix . '.addEventListener("loadeddata", function() {
                    if (!autoplayChecked' . $pano_suffix . ') {
                        checkAutoplayStatus' . $pano_suffix . '();
                    }
                });
        
                x' . $pano_suffix . '.addEventListener("canplay", function() {
                    if (!autoplayChecked' . $pano_suffix . ') {
                        checkAutoplayStatus' . $pano_suffix . '();
                    }
                });
        
                x' . $pano_suffix . '.addEventListener("canplaythrough", function() {
                    if (!autoplayChecked' . $pano_suffix . ') {
                        checkAutoplayStatus' . $pano_suffix . '();
                    }
                });
        
                setTimeout(function() {
                    if (!autoplayChecked' . $pano_suffix . ') {
                        checkAutoplayStatus' . $pano_suffix . '();
                    }
                }, 1000);
        
                x' . $pano_suffix . '.addEventListener("play", function() {
                    jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                    jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "on");
                    playing' . $pano_suffix . ' = true;
                });
        
                x' . $pano_suffix . '.addEventListener("pause", function() {
                    if (!playing' . $pano_suffix . ') {
                        jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                        jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "off");
                    }
                });
        
                function checkAutoplayStatus' . $pano_suffix . '() {
                    autoplayChecked' . $pano_suffix . ' = true;
        
                    x' . $pano_suffix . '.muted = true;
                    var playPromise = x' . $pano_suffix . '.play();
        
                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                            if (x' . $pano_suffix . '.muted || x' . $pano_suffix . '.volume === 0) {
                                handleAutoplayBlocked' . $pano_suffix . '();
                            } else {
                                autoplaySupported' . $pano_suffix . ' = true;
                                jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                                jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "on");
                                playing' . $pano_suffix . ' = true;
                            }
                        }).catch(function () {
                            handleAutoplayBlocked' . $pano_suffix . '();
                        });
                    } else {
                        setTimeout(function () {
                            if (x' . $pano_suffix . '.paused || x' . $pano_suffix . '.currentTime === 0) {
                                handleAutoplayBlocked' . $pano_suffix . '();
                            } else {
                                autoplaySupported' . $pano_suffix . ' = true;
                                jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                                jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "on");
                                playing' . $pano_suffix . ' = true;
                            }
                        }, 300);
                    }
                }
        
                function handleAutoplayBlocked' . $pano_suffix . '() {
                    autoplaySupported' . $pano_suffix . ' = false;
                    if (!alertShown' . $pano_suffix . ') {
                        alert("Autoplay is not supported in your browser. Please click the audio button to play music.");
                        alertShown' . $pano_suffix . ' = true;
                    }
        
                    x' . $pano_suffix . '.pause();
                    x' . $pano_suffix . '.currentTime = 0;
                    x' . $pano_suffix . '.muted = true;
        
                    jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-up").addClass("fas fa-volume-mute");
                    jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "off");
        
                    var musicTriggerElem = document.getElementById("' . $panoid . '");
                    if (musicTriggerElem) {
                        musicTriggerElem.addEventListener("click", musicPlay' . $pano_suffix . ');
                    }
                    document.addEventListener("touchstart", musicPlay' . $pano_suffix . ', { once: true });
                    document.addEventListener("click", musicPlay' . $pano_suffix . ', { once: true });
                }
        
                function musicPlay' . $pano_suffix . '() {
                    x' . $pano_suffix . '.muted = false;
                    var playPromise = x' . $pano_suffix . '.play();
        
                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                            jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                            jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "on");
                            playing' . $pano_suffix . ' = true;
                        }).catch(function(e) {
                            console.log("Play failed:", e);
                        });
                    } else {
                        setTimeout(function () {
                            if (!x' . $pano_suffix . '.paused) {
                                jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-mute").addClass("fas fa-volume-up");
                                jQuery("#audio_control' . $pano_suffix . '").attr("data-play", "on");
                                playing' . $pano_suffix . ' = true;
                            }
                        }, 100);
                    }
        
                    var musicTriggerElem = document.getElementById("' . $panoid . '");
                    if (musicTriggerElem) {
                        musicTriggerElem.removeEventListener("click", musicPlay' . $pano_suffix . ');
                    }
                    document.removeEventListener("touchstart", musicPlay' . $pano_suffix . ');
                    document.removeEventListener("click", musicPlay' . $pano_suffix . ');
                }
                ';
            }
        }
        $html .= '(function ($) {';
        $html .= 'jQuery(document).ready(function() {';
        $html .= 'var response = ' . $response . ';';
        $html .= 'var scenes = response[1];';
        $html .= 'if(scenes) {';
        $html .= 'var scenedata = scenes.scenes;';
        $html .= 'for(var scId in scenedata) {';
        $html .= 'if(!scenedata.hasOwnProperty(scId)) continue;';
        $html .= 'var scenehotspot = scenedata[scId].hotSpots;';
        $html .= 'if(scenehotspot && scenehotspot.length) {';
        $html .= 'for(var hIdx = 0; hIdx < scenehotspot.length; hIdx++) {';
        $html .= 'if(scenehotspot[hIdx].type === "info") {';
        $html .= '    scenehotspot[hIdx]["clickHandlerFunc"] = function(div, args) { if (typeof window.wpvrhotspot === "function") { window.wpvrhotspot(div, args); } };';
        $html .= '} else if(scenehotspot[hIdx].type === "scene") {';
        $html .= '    scenehotspot[hIdx]["clickHandlerArgs"] = scenehotspot[hIdx]["text"] || "";';
        $status = get_option('wpvr_edd_license_status');
        if ($status !== false && $status == 'valid') {
            $html .='if(typeof wpvr_public !== "undefined") {';
            $html .='if(wpvr_public.is_pro_active) {';
            $html .= '    scenehotspot[hIdx]["clickHandlerFunc"] = function(div, args) { if (typeof wpvrhotspotscene' . $pano_suffix . ' === "function") { wpvrhotspotscene' . $pano_suffix . '(div, args); } else if (typeof wpvrhotspotscene === "function") { wpvrhotspotscene(div, args); } else if (typeof window.wpvrhotspotscene === "function") { window.wpvrhotspotscene(div, args); } };';
            $html .='}';
            $html .='}';
        }
        $html .= '}';

        if (wpvr_isMobileDevice() && get_option('dis_on_hover') == "true") {
        } else {
            $html .= 'if(scenehotspot[hIdx]["createTooltipArgs"] != "") {';
            $html .= 'scenehotspot[hIdx]["createTooltipFunc"] = function(div, args) { if (typeof window.wpvrtooltip === "function") { window.wpvrtooltip(div, args); } };';
            $html .= '}';
        }

        $html .= '}';
        $html .= '}';
        $html .= '}';
        $html .= '}';
        $html .= 'var panoshow' . $pano_suffix . ';';
        $html .= 'var panoshow2' . $pano_suffix . ';';
        $html .= 'function initWPVRViewer' . $pano_suffix . '() {';
        $html .= 'if (typeof pannellum === "undefined" || typeof jQuery === "undefined") { setTimeout(initWPVRViewer' . $pano_suffix . ', 50); return; }';
        $html .= 'panoshow' . $pano_suffix . ' = pannellum.viewer(response[0]["panoid"], scenes);';
        $html .= '
            window.wpvrViewers = window.wpvrViewers || {};
            window.wpvrViewers[response[0]["panoid"]] = panoshow' . $pano_suffix . ';
            document.dispatchEvent(new CustomEvent("wpvr:viewer-ready", {
                detail: { containerId: response[0]["panoid"], viewer: panoshow' . $pano_suffix . ' }
            }));
        ';
        if ( (float) $scene_fade_duration > 0 ) {
            $html .= '
                panoshow' . $pano_suffix . '.on("scenechange", function() {
                    var fadeImage = document.querySelector("#' . $panoid . ' .pnlm-render-container > .pnlm-fade-img");
                    if (fadeImage) {
                        fadeImage.style.opacity = "1";
                        void fadeImage.offsetWidth;
                    }
                });';
        }
        $html .= '
  
        if(typeof wpvr_public === "undefined" || !wpvr_public.is_pro_active || !wpvr_public.is_license_active) {
            panoshow' . $pano_suffix . '.on("load", function() {
                jQuery(".pnlm-panorama-info").hide();
                jQuery(".pnlm-compass").hide();
            });
            
            panoshow' . $pano_suffix . '.on("scenechange", function() {
                jQuery(".pnlm-panorama-info").hide();
                jQuery(".pnlm-compass").hide();
            });
        }';
        $html .= '}';
        $html .= 'initWPVRViewer' . $pano_suffix . '();';
        //===Dplicate mode only for vr mode===//
        $response2 = json_decode($response);
        $response2[1]->compass = false;
        $response2[1]->autoRotate = false;
        $response = json_encode($response2);
        $html .= 'var response_duplicate = ' . $response . ';';
        $html .= 'var scenes_duplicate = response_duplicate[1];';

        $html .= 'if(scenes_duplicate) {';
        $html .= 'var scenedata = scenes_duplicate.scenes;';
        $html .= 'for(var scId in scenedata) {';
        $html .= 'if(!scenedata.hasOwnProperty(scId)) continue;';
        $html .= 'var scenehotspot = scenedata[scId].hotSpots;';
        $html .= 'if(scenehotspot && scenehotspot.length) {';
        $html .= 'for(var hIdx = 0; hIdx < scenehotspot.length; hIdx++) {';
        $html .= 'if(scenehotspot[hIdx]["clickHandlerArgs"] != "") {';
        $html .= 'scenehotspot[hIdx]["clickHandlerFunc"] = function(div, args) { if (typeof window.wpvrhotspot === "function") { window.wpvrhotspot(div, args); } };';
        $html .= '}';
        if (wpvr_isMobileDevice() && get_option('dis_on_hover') == "true") {
        } else {
            $html .= 'if(scenehotspot[hIdx]["createTooltipArgs"] != "") {';
            $html .= 'scenehotspot[hIdx]["createTooltipFunc"] = function(div, args) { if (typeof window.wpvrtooltip === "function") { window.wpvrtooltip(div, args); } };';
            $html .= '}';
        }
        $html .= '}';
        $html .= '}';
        $html .= '}';
        $html .= '}';

        $is_pro = apply_filters('is_wpvr_pro_active',false);
        $status  = get_option('wpvr_edd_license_status');
        $html .= 'var vr_mode = "off";';
        if ($status !== false &&  'valid' == $status  && $is_pro) {
            $html .= 'function initWPVRViewer2' . $pano_suffix . '() {';
            $html .= 'if (typeof pannellum === "undefined" || typeof jQuery === "undefined") { setTimeout(initWPVRViewer2' . $pano_suffix . ', 50); return; }';
            $html .= 'panoshow2' . $pano_suffix . ' = pannellum.viewer("' . $panoid2 . '", scenes_duplicate);';
            $html .= '
                window.wpvrViewers = window.wpvrViewers || {};
                window.wpvrViewers["' . $panoid2 . '"] = panoshow2' . $pano_suffix . ';
                document.dispatchEvent(new CustomEvent("wpvr:viewer-ready", {
                    detail: { containerId: "' . $panoid2 . '", viewer: panoshow2' . $pano_suffix . ' }
                }));
            ';
            $html .= '}';
            $html .= 'initWPVRViewer2' . $pano_suffix . '();';
// Show Cardboard Mode in Tour
            $html .= '
        var tim;
        var im = 0;
        var active_scene = "'.$default_scene.'";
        var c_time;
        c_time = new Date();
        var timer = c_time.getTime() + 2000;
       function panoShowCardBoardOnTrigger(data){
            if(scenes_duplicate) {
                var scenedata = scenes_duplicate.scenes;
                for(var i in scenedata) {
                    if(active_scene === i) {
                        var scenehotspot = scenedata[i].hotSpots;
                        if(scenehotspot && scenehotspot.length) {
                            for(var j = 0; j < scenehotspot.length; j++) {
                                var plusFiveYaw = Math.round(scenehotspot[j].yaw) + 5;
                                var minusFiveYaw = Math.round(scenehotspot[j].yaw) - 5;
                                var plusFivePitch = Math.round(scenehotspot[j].pitch) + 5;
                                var minusFivePitch = Math.round(scenehotspot[j].pitch) - 5;
                                if(Math.round(data.pitch) > minusFivePitch) {
                                    if(Math.round(data.pitch) < plusFivePitch) {
                                        if(Math.round(data.yaw) > minusFiveYaw) {
                                            if(Math.round(data.yaw) < plusFiveYaw) {
                                                jQuery(".center-pointer").addClass("wpvr-pluse-effect");
                                                var getScene = scenehotspot[j].sceneId;
                                                if(scenehotspot[j].type == "scene"){
                                                    panoshow' . $pano_suffix . '.loadScene(getScene);
                                                    panoshow2' . $pano_suffix . '.loadScene(getScene);
                                                }else{
                                                    jQuery(".center-pointer").removeClass("wpvr-pluse-effect");
                                                }
                                            } else {
                                                jQuery(".center-pointer").removeClass("wpvr-pluse-effect");
                                                c_time = new Date();
                                                timer = c_time.getTime() + 2000;
                                            }
                                        } else {
                                            jQuery(".center-pointer").removeClass("wpvr-pluse-effect");
                                            c_time = new Date();
                                            timer = c_time.getTime() + 2000;
                                        }
                                    } else {
                                        c_time = new Date();
                                        timer = c_time.getTime() + 2000;
                                    }
                                } else {
                                    c_time = new Date();
                                    timer = c_time.getTime() + 2000;
                                }
                            }
                        }
                    }
                }
            }
       };
       function vrDeviseOrientation(){
            var data = {
                pitch: panoshow' . $pano_suffix . '.getPitch(),
                yaw: panoshow' . $pano_suffix . '.getYaw(),
            };
            panoShowCardBoardOnTrigger(data);
       }';
            $html .= '
            function requestFullScreen(){
                var elem = document.getElementById("' . $master_container_id . '") || document.getElementById("master-container");
                if (elem) {
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen();
                    } else if (elem.webkitRequestFullscreen) { /* Safari */
                        elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) { /* IE11 */
                        elem.msRequestFullscreen();
                    }
                }
            }
            function requestExitFullscreen(){
                var elem = document.getElementById("' . $master_container_id . '") || document.getElementById("master-container");
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                 } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                 } else if (document.msExitFullscreen) { /* IE11 */
                    document.msExitFullscreen();
                 }
            }
            jQuery(document).on("click",".fullscreen-button .expand",function() {
                jQuery(this).hide()
                jQuery(this).parent().find(".compress").show()
                requestFullScreen()
            });   
            jQuery(document).on("click",".fullscreen-button .compress",function() {
                jQuery(this).hide()
                jQuery(this).parent().find(".expand").show()
                requestExitFullscreen()
                screen.orientation.unlock(); 
            }); 
            let onLoadAnalytics = false;
            let sceneLoadAnalytics = false;

            function storeAnalyticsData(data) {
                if (typeof wpvrAnalyticsObj !== "undefined") {
                    if (typeof wpvr_public !== "undefined") {
                        if (wpvr_public.is_pro_active) {
                            jQuery.ajax({
                                url: wpvrAnalyticsObj.ajaxUrl,
                                type: "POST",
                                data: {
                                    action: "store_scene_hotspot_data",
                                    scene_id: data.scene_id,
                                    tour_id: data.tour_id,
                                    type: data.type,
                                    hotspot_id: data.hotspot_id || "",
                                    user_agent: navigator.userAgent,
                                    device_type: getDeviceType() || "desktop",
                                    nonce: wpvrAnalyticsObj.nonce,
                                },
                                success: function (response) {
                                    console.log("Data stored successfully");
                                },
                                error: function (error) {
                                    console.log("Error in storing data");
                                }
                            });
                        }
                    }
                } else {
                    console.warn("Analytics object not available or pro not active");
                }
            }
            function getDeviceType() {
                const userAgent = navigator.userAgent.toLowerCase();
                if (/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent)) {
                    return "mobile";
                } else if (/tablet|ipad/i.test(userAgent)) {
                     return "tablet";
                } else {
                    return "desktop";
                }
            }
            panoshow' . $pano_suffix . '.on("scenechange", function(scene) {         
                onLoadAnalytics = true;
                sceneLoadAnalytics = true;
                let scene_id = scene;
                let tour_id = ' . $id . ';
                let type = "scene";
                let hotspot_id = "";
                let user_agent = navigator.userAgent;
                let device_type = getDeviceType() ? getDeviceType() : "desktop";
                storeAnalyticsData({
                    scene_id: scene_id,
                    tour_id: tour_id,
                    type: type,
                    hotspot_id: hotspot_id,
                    user_agent: user_agent,
                    device_type: device_type,
                });
            });
            panoshow' . $pano_suffix . '.on("load", function() {
                let scene_id = panoshow' . $pano_suffix . '.getScene();
                let tour_id = ' . $id . ';
                let type = "scene";
                let hotspot_id = "";
                let user_agent = navigator.userAgent;
                let device_type = getDeviceType() ? getDeviceType() : "desktop";
                if(!onLoadAnalytics) {
                    if(!sceneLoadAnalytics) {
                        storeAnalyticsData({
                            scene_id: scene_id,
                            tour_id: tour_id,
                            type: type,
                            hotspot_id: hotspot_id,
                            user_agent: user_agent,
                            device_type: device_type,
                        });
                    }
                }
            });
            function wpvrhotspotscene' . $pano_suffix . '(hotSpotDiv, args) {
                onLoadAnalytics = true;
                let scene_id = panoshow' . $pano_suffix . '.getScene();
                let tour_id = ' . $id . ';
                let type = "hotspot";
                let hotspot_id = args;
                let user_agent = navigator.userAgent;
                let device_type = getDeviceType() ? getDeviceType() : "desktop";
                storeAnalyticsData({
                    scene_id: scene_id,
                    tour_id: tour_id,
                    type: type,
                    hotspot_id: hotspot_id,
                    user_agent: user_agent,
                    device_type: device_type
                });
            }
            var wpvrhotspotscene = wpvrhotspotscene' . $pano_suffix . ';
            ';
            $html .= 'panoshow' . $pano_suffix . '.on("scenechange", function (scene){
            jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
            active_scene = scene;
            // if(localStorage.getItem("vr_mode") == "on") {
            if(vr_mode == "on") {
                jQuery("#' . $panoid2 . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                jQuery("#' . $panoid . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
             }
        });
        var compassBlock = "";
        var infoBlock = "";
        jQuery(document).on("click",".vr_mode_change' . $pano_suffix . '",function (){
          jQuery("#' . $panoid2 . ' .pnlm-load-button").trigger("click");
          jQuery("#' . $panoid . ' .pnlm-load-button").trigger("click");
          var getValue =   jQuery(this).val();
          var getParent = jQuery(this).parent().parent();
          var compass = getParent.find("#' . $panoid2 . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display");
          var panoInfo = getParent.find("#' . $panoid . ' .pnlm-panorama-info").css("display");
          if(compass == "block"){
            compassBlock = "block";
          }
          if(panoInfo == "block"){
            infoBlock = "block";
          }
            if (getValue == "off") {
                requestFullScreen()
                screen.orientation.lock("landscape-primary").then(function() {
                }).catch(function(error) {
                    alert("VR Glass Mode not supported in this device");
                });
                // localStorage.setItem("vr_mode", "on");
                vr_mode = "on";
                jQuery(".vr-mode-title").show();
                jQuery(this).val("on");
                getParent.find("#' . $panoid2 . '").css({
                    "opacity": "1", 
                    "visibility": "visible",
                    "position": "relative",
                });
                gyroSwitch = true;
                panoshow' . $pano_suffix . '.startOrientation();
                panoshow2' . $pano_suffix . '.startOrientation();
                panoshow2' . $pano_suffix . '.setPitch(panoshow' . $pano_suffix . '.getPitch(), 0);
                panoshow2' . $pano_suffix . '.setYaw(panoshow' . $pano_suffix . '.getYaw(), 0);
                getParent.find(".pano-wrap").addClass("wpvr-cardboard-disable-event");
                getParent.find("#' . $panoid . ' #zoom-in-out-controls' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #controls' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #explainer_button_' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #generic_form_button_' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #floor_map_button_' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #vrgcontrols' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #sccontrols' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #adcontrol' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' .wpvr_slider_nav").hide();
                getParent.find("#' . $panoid . ' #cp-logo-controls").hide();
                getParent.find("#' . $panoid . ' #wpvr-social-share-bg-box' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid2 . ' .pnlm-controls-container").hide();
                getParent.find("#' . $panoid . ' .pnlm-controls-container").hide();
                getParent.find("#' . $panoid2 . ' .pnlm-compass.pnlm-controls.pnlm-control").hide();
                getParent.find("#' . $panoid . ' .pnlm-compass.pnlm-controls.pnlm-control").hide();
                getParent.find("#' . $panoid2 . ' .pnlm-panorama-info").hide();
                getParent.find("#' . $panoid . ' .pnlm-panorama-info").hide();
                getParent.find("#' . $panoid . '").addClass("cardboard-half"); 
                getParent.find("#center-pointer' . $pano_suffix . '").show();
                getParent.find(".fullscreen-button").hide();
                getParent.find("#' . $panoid . ' #custom-scene-navigation' . $pano_suffix . '").hide();
                if (window.DeviceOrientationEvent) {
                    window.addEventListener("deviceorientation", vrDeviseOrientation);
                }
                 panoshow' . $pano_suffix . '.on("zoomchange", function (data){
                    panoshow2' . $pano_suffix . '.setHfov(data, 0);
                });
                panoshow2' . $pano_suffix . '.on("zoomchange", function (data){
                    panoshow' . $pano_suffix . '.setHfov(data, 0);
                });
                jQuery(document).on("click","#' . $panoid2 . '",function(event) {
                  panoshow' . $pano_suffix . '.startOrientation();
                  panoshow2' . $pano_suffix . '.startOrientation();
                });
                jQuery(document).on("click","#' . $panoid . '",function(event) {
                  panoshow' . $pano_suffix . '.startOrientation();
                  panoshow2' . $pano_suffix . '.startOrientation();
                });
                panoshow' . $pano_suffix . '.on("mousemove", function (data){
                    panoshow2' . $pano_suffix . '.setPitch(data.pitch, 0);
                    panoshow2' . $pano_suffix . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
                });
                panoshow2' . $pano_suffix . '.on("mousemove", function (data){
                    panoshow' . $pano_suffix . '.setPitch(data.pitch, 0);
                    panoshow' . $pano_suffix . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
                });
                panoshow' . $pano_suffix . '.on("touchmove", function (data){
                    panoshow' . $pano_suffix . '.stopOrientation();
                    panoshow2' . $pano_suffix . '.stopOrientation();
                    panoshow2' . $pano_suffix . '.setPitch(data.pitch, 0);
                    panoshow2' . $pano_suffix . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
                });
                panoshow2' . $pano_suffix . '.on("touchmove", function (data){
                    panoshow' . $pano_suffix . '.stopOrientation();
                    panoshow2' . $pano_suffix . '.stopOrientation();
                    panoshow' . $pano_suffix . '.setPitch(data.pitch, 0);
                    panoshow' . $pano_suffix . '.setYaw(data.yaw, 0);
                    panoShowCardBoardOnTrigger(data);
                });   
            } else if(getValue == "on") {
                screen.orientation.unlock();
                requestExitFullscreen();
                // localStorage.setItem("vr_mode", "off");
                vr_mode = "off";
                jQuery(".vr-mode-title").hide();
                jQuery(this).val("off");
                getParent.find("#' . $panoid2 . '").css({
                    "opacity": "0", 
                    "visibility": "hidden",
                    "position": "absolute",
                });
                getParent.find(".pano-wrap").removeClass("wpvr-cardboard-disable-event");
                getParent.find("#' . $panoid . ' #zoom-in-out-controls' . $pano_suffix . '").show();
                getParent.find("#' . $panoid . ' #controls' . $pano_suffix . '").show();
                getParent.find("#' . $panoid . ' #explainer_button_' . $pano_suffix . '").show();
                getParent.find("#' . $panoid . ' #generic_form_button_' . $pano_suffix . '").show();
                getParent.find("#' . $panoid . ' #floor_map_button_' . $pano_suffix . '").show();
                getParent.find("#' . $panoid2 . ' .pnlm-controls-container").show();
                getParent.find("#' . $panoid . ' .pnlm-controls-container").show();
                getParent.find("#' . $panoid . ' #vrgcontrols' . $pano_suffix . '").show();
                getParent.find("#' . $panoid . ' #sccontrols' . $pano_suffix . '").hide();
                getParent.find("#' . $panoid . ' #adcontrol' . $pano_suffix . '").show();
                getParent.find("#' . $panoid . ' .wpvr_slider_nav").hide();
                getParent.find("#' . $panoid . ' #cp-logo-controls").show();
                getParent.find("#' . $panoid . ' #wpvr-social-share-bg-box' . $pano_suffix . '").show();
                 getParent.find("#' . $panoid . ' #custom-scene-navigation' . $pano_suffix . '").show();
                if(compassBlock == "block"){
                    getParent.find("#' . $panoid2 . ' .pnlm-compass.pnlm-controls.pnlm-control").show();
                    getParent.find("#' . $panoid . ' .pnlm-compass.pnlm-controls.pnlm-control").show();
                }
                if(infoBlock == "block"){
                    getParent.find("#' . $panoid2 . ' .pnlm-panorama-info").show();
                    getParent.find("#' . $panoid . ' .pnlm-panorama-info").show();
                }
                getParent.find("#' . $panoid . '").removeClass("cardboard-half");
                getParent.find("#center-pointer' . $pano_suffix . '").hide();
                getParent.find(".fullscreen-button").hide();
                panoshow' . $pano_suffix . '.off("mousemove");
                panoshow' . $pano_suffix . '.off("touchmove");
                panoshow2' . $pano_suffix . '.off("mousemove");
                panoshow2' . $pano_suffix . '.off("touchmove");
                if (window.DeviceOrientationEvent) {
                    window.removeEventListener("deviceorientation", vrDeviseOrientation);
                }
            }
        });';
            $html .= 'panoshow2' . $pano_suffix . '.on("load", function (){
                // if(localStorage.getItem("vr_mode") == "off") {
                if( vr_mode == "off") {
                      jQuery(".vr-mode-title").hide();
                    }
                 else {
                    jQuery("#' . $panoid2 . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                    jQuery("#' . $panoid . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                    jQuery("#' . $panoid2 . ' .pnlm-panorama-info").hide();
                    jQuery("#' . $panoid . ' .pnlm-panorama-info").hide();
                    jQuery(".vr-mode-title").show();
                 }
			});';
        }
        //=== end Dplicate mode only for vr mode===//
        $html .= 'jQuery("#' . $panoid . ' .wpvr-floor-map .floor-plan-pointer").on("click",function(){
           var scene_id = jQuery(this).attr("scene_id");
           panoshow' . $pano_suffix . '.loadScene(scene_id)
           jQuery(".floor-plan-pointer").removeClass("add-pulse")
           jQuery(this).addClass("add-pulse")
        });';
        if ($scene_animation_enabled && is_plugin_active('wpvr-pro/wpvr-pro.php')) {
            $animation_type = $postdata['sceneAnimationName'] ?? 'none';
            $animation_type = $animation_type === 'fade' ? 'fade_in' : $animation_type;
            $animationDuration = $postdata['sceneAnimationTransitionDuration'] ?? '500ms';
            $animationDelay = $postdata['sceneAnimationTransitionDelay'] ?? '0ms';
            $animation_js = apply_filters('wpvr_scene_animation_js', $id, $animation_type, $animationDuration, $animationDelay);
            if (!empty($animation_js)) {
                $html .= $animation_js;
                $html .= 'panoshow' . $pano_suffix . '.on("load", function (scene){
                    if (typeof changeScene === "function") {
                        changeScene();
                    } else {
                        console.warn("changeScene function is not defined.");
                    }
                });';
            }
        }

        $html .= 'panoshow' . $pano_suffix . '.on("mousemove", function (data){
            jQuery(".add-pulse").css({"transform":"rotate("+data.yaw+"deg)"});
        });
    ';
        $status  = get_option('wpvr_edd_license_status');
        if ($status !== false &&  'valid' == $status  && $is_pro){
            $html .= 'panoshow' . $pano_suffix . '.on("scenechange", function (scene){
            jQuery(".center-pointer").removeClass("wpvr-pluse-effect")
            jQuery(".floor-plan-pointer").each(function(index ,element){
                var scene_id = jQuery(this).attr("scene_id");
                if( active_scene == scene_id ){
                    jQuery(".floor-plan-pointer").removeClass("add-pulse")
                    jQuery(this).addClass("add-pulse")
                }
            });
        });';
            $html .= 'panoshow' . $pano_suffix . '.on("load", function (){
           if(jQuery(".floor-plan-pointer").length > 0){
               jQuery(".floor-plan-pointer").each(function(index ,element){
                    var scene_id = jQuery(this).attr("scene_id");
                    if( active_scene == scene_id ){
                        jQuery(".floor-plan-pointer").removeClass("add-pulse")
                        jQuery(this).addClass("add-pulse")
                    }
                });
           }
        });';
        }
        if ($status !== false &&  'valid' == $status  && $is_pro){
            $scene_navigation_content_type = isset($postdata['scene_navigation_content_type']) ? $postdata['scene_navigation_content_type'] : 'scene_id';
            $html .= 'jQuery("#' . $panoid . ' .custom-scene-navigation").on("click", function() {
                jQuery("#custom-scene-navigation-nav' . $pano_suffix . ' ul").empty();
                if (scenes) {
                    var scene_navigation_content_type = "' . $scene_navigation_content_type . '";
                    var sceneList = scenes.scenes;
                    var getScene = panoshow' . $pano_suffix . '.getScene();
                    for (const key in sceneList) {
                        let title;
                        if (scene_navigation_content_type === "scene_title") {
                            if (sceneList[key].title) {
                                title = sceneList[key].title;
                            } else {
                                if (title == "" || title == undefined) {
                                    if (sceneList[key].panorama) {
                                        title = getImageNameWithoutExtension(sceneList[key].panorama);
                                    }
                                }
                            }
                        } else if (scene_navigation_content_type === "scene_image_name") {
                            if (sceneList[key].panorama) {
                                title = getImageNameWithoutExtension(sceneList[key].panorama);
                            }
                        } else {
                            title = key;
                        }
                        if (sceneList.hasOwnProperty(key)) {
                            let ulElement = document.querySelector("#custom-scene-navigation-nav' . $pano_suffix . ' ul");
                            if (ulElement) {
                                let liElement = document.createElement("li");
                                liElement.className = "scene-navigation-list" + (key === getScene ? " active" : "");
                                liElement.setAttribute("scene_id", key);
                                liElement.textContent = title;
                                ulElement.appendChild(liElement);
                            }
                        }
                    }
                    jQuery("#custom-scene-navigation-nav' . $pano_suffix . '").toggleClass("visible");
                }
            });';
            $html .='function getImageNameWithoutExtension(imageUrl) {
                    // Split the URL by "/"
                    var parts = imageUrl.split("/");
                    // Get the last part which contains the image name
                    var imageNameWithExtension = parts[parts.length - 1];
                    // Split the image name by period (.)
                    var imageNameParts = imageNameWithExtension.split(".");
                    // Remove the last part (which is the extension) and join the remaining parts
                    var imageNameWithoutExtension = imageNameParts.slice(0, -1).join(".");
                    // Return the image name without extension
                    return imageNameWithoutExtension;
                }';
            $html .= 'jQuery("#' . $panoid . ' #custom-scene-navigation-nav' . $pano_suffix . ' ul").on("click", "li.scene-navigation-list", function() {
            if (scenes) {
                jQuery(this).siblings("li").removeClass("active");
                jQuery(this).addClass("active");
                var scene_key = jQuery(this).attr("scene_id");
                panoshow' . $pano_suffix . '.loadScene(scene_key);
            }
        });';
        }
        $html .= 'const node = document.querySelector(".add-pulse");
        panoshow' . $pano_suffix . '.on("compasschange", function (data){
            // const node = document.querySelector(".add-pulse");
            // node.style.transform = data;
            // jQuery(".add-pulse").css({"transform":data});
            });';
        $html .= 'panoshow' . $pano_suffix . '.on("load", function (){
            // if(localStorage.getItem("vr_mode") == "off") {
            if(vr_mode == "off") {
                  jQuery(".vr-mode-title").hide();
                } else {
                jQuery("#' . $panoid2 . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                jQuery("#' . $panoid . ' .pnlm-compass.pnlm-controls.pnlm-control").css("display","none");
                jQuery("#' . $panoid2 . ' .pnlm-panorama-info").hide();
                jQuery("#' . $panoid . ' .pnlm-panorama-info").hide();
                jQuery(".vr-mode-title").show();
             }
            setTimeout(() => {
                window.dispatchEvent(new Event("resize"));
            }, 200);
						if (jQuery("#' . $panoid . '").children().children(".pnlm-panorama-info:visible").length > 0) {
	               jQuery("#controls' . $pano_suffix . '").css("bottom", "80px");
	           }
	           else {
	             jQuery("#controls' . $pano_suffix . '").css("bottom", "5px");
	           }
					});';
        $html .= 'panoshow' . $pano_suffix . '.on("render", function (){
              window.dispatchEvent(new Event("resize"));
            });';
        $html .= 'if (scenes.autoRotate) {
                        var wpvrAutoRotateTimer' . $pano_suffix . ' = null;
                        var wpvrAutoRotateStopDelay' . $pano_suffix . ' = parseInt(scenes.autoRotateStopDelay, 10) || 0;
                        var wpvrScheduleAutoRotate' . $pano_suffix . ' = function () {
                            clearTimeout(wpvrAutoRotateTimer' . $pano_suffix . ');
                            if (wpvrAutoRotateStopDelay' . $pano_suffix . ' > 0) {
                                wpvrAutoRotateTimer' . $pano_suffix . ' = setTimeout(function () {
                                    panoshow' . $pano_suffix . '.stopAutoRotate();
                                }, wpvrAutoRotateStopDelay' . $pano_suffix . ');
                            } else {
                                wpvrAutoRotateTimer' . $pano_suffix . ' = setTimeout(function () {
                                    panoshow' . $pano_suffix . '.startAutoRotate(scenes.autoRotate, 0);
                                }, 3000);
                            }
                        };
                        panoshow' . $pano_suffix . '.on("load", wpvrScheduleAutoRotate' . $pano_suffix . ');
                        panoshow' . $pano_suffix . '.on("scenechange", wpvrScheduleAutoRotate' . $pano_suffix . ');
                    }';
        $html .= 'var touchtime = 0;';
        $html .= '
            var wpvrHotspotRoot' . $pano_suffix . ' = document.getElementById("' . $panoid . '");
            if (wpvrHotspotRoot' . $pano_suffix . ') {
                wpvrHotspotRoot' . $pano_suffix . '.addEventListener("click", function (event) {
                    var hotspot = event.target.closest(".pnlm-hotspot-base");

                    if (!hotspot) {
                        return;
                    }

                    var hotspotLink = hotspot.closest("a[href]");
                    if (!hotspotLink) {
                        hotspotLink = hotspot.querySelector("a[href]");
                    }

                    if (!hotspotLink) {
                        return;
                    }

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    if ((hotspotLink.getAttribute("target") || "_blank") === "_self") {
                        window.location.assign(hotspotLink.href);
                    } else {
                        window.open(hotspotLink.href, "_blank", "noopener,noreferrer");
                    }
                }, true);
            }
        ';
        if ($vrgallery) {
            $gallery_scene_ids = [];
            if (isset($panodata["scene-list"])) {
                foreach ($panodata["scene-list"] as $panoscenes) {
                    $scene_key = $panoscenes['scene-id'];
                    $gallery_scene_ids[] = (string) $scene_key;
                    $scene_key_gallery = $panoscenes['scene-id'] . '_gallery_' . $pano_suffix;
                    $html .= 'jQuery(document).on("click","#' . $scene_key_gallery . '",function() {
                        panoshow' . $pano_suffix . '.loadScene(' . wp_json_encode( (string) $scene_key ) . ');
    		        });';
                }
            }

            $is_modern_gallery = isset($postdata['tourLayout']['layout']) && 'layout1' === $postdata['tourLayout']['layout'];
            $gallery_options = $is_modern_gallery
                ? '{
                    loop: false,
                    nav: false,
                    dots: false,
                    margin: 18,
                    mouseDrag: false,
                    responsive: {
                        0: { items: 2 },
                        320: { items: 3 },
                        500: { items: 4 },
                        690: { items: 5 }
                    }
                }'
                : '{
                    autoWidth: true,
                    loop: false,
                    nav: false,
                    dots: false,
                    margin: 10,
                    stagePadding: 0
                }';

            $html .= '
                var wpvrGallery' . $pano_suffix . ' = jQuery("#sccontrols' . $pano_suffix . '");
                var wpvrGalleryRoot' . $pano_suffix . ' = jQuery("#' . $panoid . '");
                var wpvrGalleryScenes' . $pano_suffix . ' = ' . wp_json_encode(array_values($gallery_scene_ids)) . ';
                var wpvrGalleryResizeTimer' . $pano_suffix . ';
                var wpvrGalleryVisibilityTimer' . $pano_suffix . ';
                var wpvrGalleryObserver' . $pano_suffix . ';

                function wpvrPrepareSceneNavigation' . $pano_suffix . '() {
                    var navigationDisabled = wpvrGalleryScenes' . $pano_suffix . '.length < 2;
                    var previousButtons = wpvrGalleryRoot' . $pano_suffix . '.find(".wpvr_owl_prev");
                    var nextButtons = wpvrGalleryRoot' . $pano_suffix . '.find(".wpvr_owl_next");

                    wpvrGallery' . $pano_suffix . '.removeClass("owl-theme");
                    wpvrGalleryRoot' . $pano_suffix . '.find(".wpvr_slider_nav").removeClass("owl-nav");
                    previousButtons
                        .removeClass("owl-prev")
                        .prop("disabled", navigationDisabled)
                        .toggleClass("disabled", navigationDisabled);
                    nextButtons
                        .removeClass("owl-next")
                        .prop("disabled", navigationDisabled)
                        .toggleClass("disabled", navigationDisabled);
                }

                function wpvrRefreshGallery' . $pano_suffix . '() {
                    wpvrPrepareSceneNavigation' . $pano_suffix . '();

                    if (!wpvrGallery' . $pano_suffix . '.length || !wpvrGallery' . $pano_suffix . '.hasClass("owl-loaded") || !wpvrGallery' . $pano_suffix . '.is(":visible")) {
                        return;
                    }

                    if (wpvrGallery' . $pano_suffix . '.css("display") === "flex") {
                        wpvrGallery' . $pano_suffix . '.css("display", "block");
                    }
                    wpvrGallery' . $pano_suffix . '.trigger("refresh.owl.carousel");
                    wpvrUpdateGalleryNavigation' . $pano_suffix . '(panoshow' . $pano_suffix . '.getScene());
                }

                function wpvrGallerySceneIndex' . $pano_suffix . '(sceneId) {
                    return wpvrGalleryScenes' . $pano_suffix . '.indexOf(String(sceneId));
                }

                function wpvrUpdateGalleryNavigation' . $pano_suffix . '(sceneId) {
                    var currentIndex = wpvrGallerySceneIndex' . $pano_suffix . '(sceneId);
                    var activeThumbnailId = String(sceneId) + "_gallery_' . $pano_suffix . '";
                    var thumbnails = wpvrGallery' . $pano_suffix . '.find("img.scctrl");
                    var activeThumbnail = thumbnails.filter(function () {
                        return this.id === activeThumbnailId;
                    });

                    wpvrPrepareSceneNavigation' . $pano_suffix . '();
                    thumbnails.removeClass("wpvr-active-thumbnail");
                    wpvrGallery' . $pano_suffix . '.find(".owl-item").removeClass("clicked");
                    activeThumbnail.addClass("wpvr-active-thumbnail");
                    activeThumbnail.closest(".owl-item").addClass("clicked");

                    if (currentIndex >= 0) {
                        if (wpvrGallery' . $pano_suffix . '.hasClass("owl-loaded")) {
                            wpvrGallery' . $pano_suffix . '.trigger("to.owl.carousel", [currentIndex, 300, true]);
                            window.setTimeout(wpvrPrepareSceneNavigation' . $pano_suffix . ', 0);
                        }
                    }
                }

                function wpvrLoadAdjacentGalleryScene' . $pano_suffix . '(direction) {
                    var currentIndex = wpvrGallerySceneIndex' . $pano_suffix . '(panoshow' . $pano_suffix . '.getScene());

                    if (currentIndex < 0 || wpvrGalleryScenes' . $pano_suffix . '.length < 2) {
                        return;
                    }

                    var targetIndex = (currentIndex + direction + wpvrGalleryScenes' . $pano_suffix . '.length) % wpvrGalleryScenes' . $pano_suffix . '.length;
                    panoshow' . $pano_suffix . '.loadScene(wpvrGalleryScenes' . $pano_suffix . '[targetIndex]);
                }

                if (jQuery.fn.owlCarousel) {
                    if (wpvrGallery' . $pano_suffix . '.length) {
                        if (!wpvrGallery' . $pano_suffix . '.hasClass("owl-loaded")) {
                            wpvrGallery' . $pano_suffix . '.owlCarousel(' . $gallery_options . ');
                        }
                    }
                }

                wpvrGalleryRoot' . $pano_suffix . '
                    .off("click.wpvrGalleryRefresh' . $pano_suffix . '", "#vrgcontrols' . $pano_suffix . '")
                    .on("click.wpvrGalleryRefresh' . $pano_suffix . '", "#vrgcontrols' . $pano_suffix . '", function () {
                        wpvrPrepareSceneNavigation' . $pano_suffix . '();
                        window.setTimeout(wpvrRefreshGallery' . $pano_suffix . ', 450);
                    });

                if (wpvrGalleryRoot' . $pano_suffix . '.length) {
                    wpvrGalleryRoot' . $pano_suffix . '[0].addEventListener("click", function (event) {
                        var navigationButton = event.target.closest(".wpvr_owl_prev, .wpvr_owl_next");

                        if (!navigationButton) {
                            return;
                        }

                        if (!wpvrGalleryRoot' . $pano_suffix . '[0].contains(navigationButton)) {
                            return;
                        }

                        event.preventDefault();
                        event.stopImmediatePropagation();

                        if (navigationButton.classList.contains("wpvr_owl_prev")) {
                            wpvrLoadAdjacentGalleryScene' . $pano_suffix . '(-1);
                        } else {
                            wpvrLoadAdjacentGalleryScene' . $pano_suffix . '(1);
                        }
                    }, true);
                }

                jQuery(window)
                    .off("resize.wpvrGallery' . $pano_suffix . '")
                    .on("resize.wpvrGallery' . $pano_suffix . '", function () {
                        window.clearTimeout(wpvrGalleryResizeTimer' . $pano_suffix . ');
                        wpvrGalleryResizeTimer' . $pano_suffix . ' = window.setTimeout(wpvrRefreshGallery' . $pano_suffix . ', 100);
                    });

                if (window.MutationObserver) {
                    if (wpvrGallery' . $pano_suffix . '.length) {
                        wpvrGalleryObserver' . $pano_suffix . ' = new MutationObserver(function () {
                            window.clearTimeout(wpvrGalleryVisibilityTimer' . $pano_suffix . ');
                            wpvrGalleryVisibilityTimer' . $pano_suffix . ' = window.setTimeout(wpvrRefreshGallery' . $pano_suffix . ', 50);
                        });
                        wpvrGalleryObserver' . $pano_suffix . '.observe(wpvrGallery' . $pano_suffix . '[0], {
                            attributes: true,
                            attributeFilter: ["style"]
                        });
                    }
                }

                panoshow' . $pano_suffix . '.on("scenechange", function (sceneId) {
                    wpvrUpdateGalleryNavigation' . $pano_suffix . '(sceneId);
                });

                window.setTimeout(wpvrRefreshGallery' . $pano_suffix . ', 0);
                window.setTimeout(function () {
                    wpvrUpdateGalleryNavigation' . $pano_suffix . '(panoshow' . $pano_suffix . '.getScene());
                }, 0);
            ';
        }
        //===Custom Control===//
        if (isset($custom_control)) {
            if ($custom_control['panupSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                $html .= 'document.getElementById("pan-up' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.setPitch(panoshow' . $pano_suffix . '.getPitch() + 10);';
                $html .= '});';
            }
            if ($custom_control['panDownSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                $html .= 'document.getElementById("pan-down' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.setPitch(panoshow' . $pano_suffix . '.getPitch() - 10);';
                $html .= '});';
            }
            if ($custom_control['panLeftSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                $html .= 'document.getElementById("pan-left' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.setYaw(panoshow' . $pano_suffix . '.getYaw() - 10);';
                $html .= '});';
            }
            if ($custom_control['panRightSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                $html .= 'document.getElementById("pan-right' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.setYaw(panoshow' . $pano_suffix . '.getYaw() + 10);';
                $html .= '});';
            }
            if ($custom_control['panZoomInSwitch'] == "on") {
                $html .= 'document.getElementById("zoom-in' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.setHfov(panoshow' . $pano_suffix . '.getHfov() - 10);';
                $html .= '});';
            }
            if ($custom_control['panZoomOutSwitch'] == "on") {
                $html .= 'document.getElementById("zoom-out' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.setHfov(panoshow' . $pano_suffix . '.getHfov() + 10);';
                $html .= '});';
            }
            if ($custom_control['panFullscreenSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                $html .= 'document.getElementById("fullscreen' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.toggleFullscreen();';
                $html .= '});';
                $html .= '(function() {';
                $html .= 'function wpvrFsChange' . $pano_suffix . '() {';
                $html .= 'var fsIcon = document.querySelector("#fullscreen' . $pano_suffix . ' i");';
                $html .= 'if (!fsIcon) return;';
                $html .= 'if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {';
                $html .= 'fsIcon.classList.remove("fa-expand"); fsIcon.classList.add("fa-minimize");';
                $html .= '} else {';
                $html .= 'fsIcon.classList.remove("fa-minimize"); fsIcon.classList.add("fa-expand");';
                $html .= '}';
                $html .= '}';
                $html .= 'document.addEventListener("fullscreenchange", wpvrFsChange' . $pano_suffix . ');';
                $html .= 'document.addEventListener("webkitfullscreenchange", wpvrFsChange' . $pano_suffix . ');';
                $html .= 'document.addEventListener("mozfullscreenchange", wpvrFsChange' . $pano_suffix . ');';
                $html .= 'document.addEventListener("MSFullscreenChange", wpvrFsChange' . $pano_suffix . ');';
                $html .= '})();';
            }
            if ($custom_control['backToHomeSwitch'] == "on" && 'valid' == $status  && $is_pro) {
                $html .= 'document.getElementById("backToHome' . $pano_suffix . '").addEventListener("click", function(e) {';
                $html .= 'panoshow' . $pano_suffix . '.loadScene(' . wp_json_encode( (string) $default_scene ) . ');';
                $html .= '});';
            }
            if ($gyro_button_enabled && 'valid' == $status  && $is_pro) {
                $html .= '
                    (function() {
                        var gyroButton = document.getElementById("gyroscope' . $pano_suffix . '");
                        var gyroIcon = gyroButton ? gyroButton.querySelector("i") : null;
                        var activeColor = ' . wp_json_encode($custom_control['gyroscopeColor']) . ';
                        var permissionGranted = false;

                        if (!gyroButton || !gyroIcon) {
                            return;
                        }

                        function updateGyroscopeState() {
                            gyroIcon.style.color = panoshow' . $pano_suffix . '.isOrientationActive()
                                ? activeColor
                                : "red";
                        }

                        function startGyroscope() {
                            if (!panoshow' . $pano_suffix . '.isOrientationSupported()) {
                                updateGyroscopeState();
                                return;
                            }

                            panoshow' . $pano_suffix . '.startOrientation();
                            window.setTimeout(updateGyroscopeState, 0);
                        }

                        function requestGyroscopePermission() {
                            var orientationEvent = window.DeviceOrientationEvent;
                            if (orientationEvent) {
                                if (typeof orientationEvent.requestPermission === "function") {
                                    if (!permissionGranted) {
                                        orientationEvent.requestPermission()
                                            .then(function(state) {
                                                permissionGranted = state === "granted";
                                                if (permissionGranted) {
                                                    startGyroscope();
                                                } else {
                                                    updateGyroscopeState();
                                                }
                                            })
                                            .catch(updateGyroscopeState);
                                        return;
                                    }
                                }
                            }

                            startGyroscope();
                        }

                        panoshow' . $pano_suffix . '.on("load", updateGyroscopeState);
                        panoshow' . $pano_suffix . '.on("scenechange", updateGyroscopeState);

                        gyroButton.addEventListener("click", function() {
                            if (panoshow' . $pano_suffix . '.isOrientationActive()) {
                                panoshow' . $pano_suffix . '.stopOrientation();
                                updateGyroscopeState();
                                return;
                            }

                            requestGyroscopePermission();
                        });
                    })();';
            }
        }
        $angle_up = '<i class="fa fa-angle-up"></i>';
        $angle_down = '<i class="fa fa-angle-down"></i>';
        $sin_qout = "'";

        //===Explainer Script===//

        if ($autoplay_bg_music == 'on') {
            $html .= 'jQuery(document).on("click","#explainer_button_' . $pano_suffix . '",function() {
                jQuery("#explainer' . $pano_suffix . '").slideToggle();
                playing' . $pano_suffix . ' = false;
                var x' . $pano_suffix . ' = document.getElementById("vrAudio' . $pano_suffix . '");
                jQuery("#vr-volume' . $pano_suffix . '").removeClass("fas fa-volume-up");
                jQuery("#vr-volume' . $pano_suffix . '").addClass("fas fa-volume-mute");
                x' . $pano_suffix . '.pause();
            });
            jQuery(document).on("click",".close-explainer-video",function() {
                jQuery(this).parent(".explainer").hide();
                var el_src = jQuery(".vr-iframe").attr("src");
                jQuery(".vr-iframe").attr("src", el_src);
              });';
        } else {
            $html .= 'jQuery(document).on("click","#explainer_button_' . $pano_suffix . '",function() {
                    jQuery("#explainer' . $pano_suffix . '").slideToggle(function(){
                    var $explainerVideoId = jQuery("#explainer' . $pano_suffix . '");
                    var $explainerVideoIframe = $explainerVideoId.find("iframe");
                    var explainerVideoIframSrc = $explainerVideoIframe.attr("src");
                    $explainerVideoIframe.attr("src", "");
                    $explainerVideoIframe.attr("src", explainerVideoIframSrc);
        });
            });
            jQuery(document).on("click", ".close-explainer-video", function() {
              var $explainer = jQuery(this).parent(".explainer");
              var $iframe = $explainer.find("iframe");
              var el_src = $iframe.attr("src");
              $iframe.attr("src", "");
              $explainer.hide();
              $iframe.attr("src", el_src);
            });';
        }

        $html .= '
      jQuery(document).on("click","#' . $panoid . '",function(event) {
        var isCross = event.target.closest(".cross");
        var isActiveModal = event.target.closest(".custom-ifram-wrapper");
        var isForm = event.target.closest(".wpvr-hotspot-tweak-contents-wrapper");
        var isHotspot = event.target.closest(".pnlm-hotspot-base");
        if(isCross != null){
            return;
        }
        if(isForm != null){
            jQuery(this).addClass("show-modal");
        }else if(isActiveModal == null){
            if(isHotspot == null){
                jQuery(".custom-ifram-wrapper .custom-ifram").empty();
                jQuery(".custom-ifram-wrapper").hide();
                jQuery(this).removeClass("show-modal");
                jQuery(".wpvr-hotspot-tweak-contents-wrapper").hide();
            }
        }
      });';

        //===Explainer Script End===//

        //===generic form script===//
        if (isset($postdata["genericform"]) && $postdata["genericform"] === 'on') {
            $html .= '
    jQuery(document).on("click","#generic_form_button_' . $pano_suffix . '",function() {
      jQuery("#wpvr-generic-form' . $pano_suffix . '").fadeToggle();
    });

    jQuery(document).on("click",".close-generic-form",function() {
      jQuery(this).parent(".wpvr-generic-form").fadeOut()
    });
    ';
        }
        //===generic from script===//

        //===Floor map  Script===//
        $html .= 'jQuery(document).on("click","#floor_map_button_' . $pano_suffix . '",function() {
                jQuery("#wpvr-floor-map' . $pano_suffix . '").toggle().removeClass("fullwindow");
              });
              jQuery(document).on("dblclick","#wpvr-floor-map' . $pano_suffix . '",function(){
                jQuery(this).addClass("fullwindow");
                jQuery(this).parents(".pano-wrap").addClass("show-modal");
              });
              jQuery(document).on("click",".close-floor-map-plan",function() {
                jQuery(this).parent(".wpvr-floor-map").hide();
                jQuery(this).parent(".wpvr-floor-map").removeClass("fullwindow");
                jQuery(this).parents(".pano-wrap").removeClass("show-modal");
              });';
        //===Floor map Script End===//

        if ($vrgallery_display) {

            if (!$autoload) {
                $html .= 'jQuery(document).ready(function($){
                    jQuery("#sccontrols' . $pano_suffix . '").hide();
  		              jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
                    jQuery("#sccontrols' . $pano_suffix . '").hide();
                    jQuery("#' . $panoid . ' .wpvr_slider_nav").hide();
                });';
                $html .= 'var slide' . $pano_suffix . ' = "down";
    		          jQuery(document).on("click","#vrgcontrols' . $pano_suffix . '",function() {
    		            if (slide' . $pano_suffix . ' == "up") {
                                jQuery(".vrgctrl' . $pano_suffix . '").empty();
                                jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
                                slide' . $pano_suffix . ' = "down";
                                jQuery("#' . $panoid . ' .wpvr_slider_nav").slideToggle();
                                jQuery("#sccontrols' . $pano_suffix . '").slideToggle(function(){
                                if (jQuery(".elementor-edit-mode .elementor-widget-container").length) {
                                    if (jQuery(this).is(":visible")) {
                                    jQuery(".elementor-edit-mode  .elementor-widget-container .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                            "display": "flex",
                                            "justify-content": "center",
                                            "align-items": "center",
                                            "gap": "15px"
                                        });
                                    }
                                }
                                if (jQuery(".bricks-is-frontend").length) {
                                    if (jQuery(this).is(":visible")) {
                                    jQuery(".bricks-is-frontend .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                            "display": "flex",
                                            "justify-content": "center",
                                            "align-items": "center"
                                        });
                                    }
                                }
                            });
    		            }
    		            else {
                jQuery(".vrgctrl' . $pano_suffix . '").empty();
                jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
                slide' . $pano_suffix . ' = "up";
                jQuery("#' . $panoid . ' .wpvr_slider_nav").slideToggle();
               jQuery("#sccontrols' . $pano_suffix . '").slideToggle(function(){
                  if (jQuery(".elementor-edit-mode .elementor-widget-container").length) {
                    if (jQuery(this).is(":visible")) {
                        jQuery(".elementor-edit-mode  .elementor-widget-container .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                            "display": "flex",
                            "justify-content": "center",
                            "align-items": "center",
                            "gap": "15px"
                        });
                    }
                  }
                  if (jQuery(".bricks-is-frontend").length) {
                    if (jQuery(this).is(":visible")) {
                       jQuery(".bricks-is-frontend .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                            "display": "flex",
                            "justify-content": "center",
                            "align-items": "center"
                        });
                    }
                  }                  
               });
              }
            });';
            } else {
                $html .= 'jQuery(document).ready(function($){
                  jQuery("#sccontrols' . $pano_suffix . '").show();
                    jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
                    jQuery("#' . $panoid . ' .wpvr_slider_nav").show();
                });';
                $html .= 'var slide' . $pano_suffix . ' = "down";
                jQuery(document).on("click","#vrgcontrols' . $pano_suffix . '",function() {
                  if (slide' . $pano_suffix . ' == "up") {
                    jQuery(".vrgctrl' . $pano_suffix . '").empty();
                    jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
                    slide' . $pano_suffix . ' = "down";
                  } else {
                    jQuery(".vrgctrl' . $pano_suffix . '").empty();
                    jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
                    slide' . $pano_suffix . ' = "up";
                  }
                  jQuery("#' . $panoid . ' .wpvr_slider_nav").slideToggle();
                    jQuery("#sccontrols' . $pano_suffix . '").slideToggle(function(){
                        if (jQuery(".elementor-edit-mode .elementor-widget-container").length) {
                            if (jQuery(this).is(":visible")) {
                            jQuery(".elementor-edit-mode  .elementor-widget-container .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                    "display": "flex",
                                    "justify-content": "center",
                                    "align-items": "center",
                                    "gap": "15px"
                                });
                            }
                        }
                        if (jQuery(".bricks-is-frontend").length) {
                            if (jQuery(this).is(":visible")) {
                            jQuery(".bricks-is-frontend .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                    "display": "flex",
                                    "justify-content": "center",
                                    "align-items": "center"
                                });
                            }
                        }              
                    });
                });';
            }
        } else {
            $html .= 'jQuery(document).ready(function($){
		              jQuery("#sccontrols' . $pano_suffix . '").hide();
                      jQuery("#' . $panoid . ' .wpvr_slider_nav").hide();
		              jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
		          });';
            $html .= 'var slide' . $pano_suffix . ' = "down";
		          jQuery(document).on("click","#vrgcontrols' . $pano_suffix . '",function() {
		            if (slide' . $pano_suffix . ' == "up") {
		              jQuery(".vrgctrl' . $pano_suffix . '").empty();
		              jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_up . $sin_qout . ');
		              slide' . $pano_suffix . ' = "down";
		            }
		            else {
		              jQuery(".vrgctrl' . $pano_suffix . '").empty();
		              jQuery(".vrgctrl' . $pano_suffix . '").html(' . $sin_qout . $angle_down . $sin_qout . ');
		              slide' . $pano_suffix . ' = "up";
		            }
                    jQuery("#' . $panoid . ' .wpvr_slider_nav").slideToggle(); 
                    jQuery("#sccontrols' . $pano_suffix . '").slideToggle(function(){
                        if (jQuery(".elementor-edit-mode .elementor-widget-container").length) {
                            if (jQuery(this).is(":visible")) {
                                jQuery(".elementor-edit-mode  .elementor-widget-container .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                    "display": "flex",
                                    "justify-content": "center",
                                    "align-items": "center",
                                    "gap": "15px"
                                });
                            }
                        }
                        if (jQuery(".bricks-is-frontend").length) {
                            if (jQuery(this).is(":visible")) {
                            jQuery(".bricks-is-frontend .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                    "display": "flex",
                                    "justify-content": "center",
                                    "align-items": "center"
                                });
                            }
                        }              
                    });
          });';
        }
        if (!$autoload) {
            $html .= 'jQuery(document).ready(function(){
                    jQuery("#controls' . $pano_suffix . '").hide();
                    jQuery("#zoom-in-out-controls' . $pano_suffix . '").hide();
                    jQuery("#adcontrol' . $pano_suffix . '").hide();
                    jQuery("#explainer_button_' . $pano_suffix . '").hide();
                    jQuery("#generic_form_button_' . $pano_suffix . '").hide();
                    jQuery("#floor_map_button_' . $pano_suffix . '").hide();
                    jQuery("#vrgcontrols' . $pano_suffix . '").hide();
                    jQuery("#cp-logo-controls").hide();
                    jQuery(".custom-scene-navigation").hide();
                    jQuery("#' . $panoid . '").find(".pnlm-panorama-info").hide();
                });';
            if ($vrgallery_display) {
                $html .= 'var load_once' . $pano_suffix . ' = "true";';
                $html .= 'panoshow' . $pano_suffix . '.on("load", function (){
                      if (load_once' . $pano_suffix . ' == "true") {
                        load_once' . $pano_suffix . ' = "false";
                       jQuery("#sccontrols' . $pano_suffix . '").slideToggle(function(){
                          if (jQuery(".elementor-edit-mode .elementor-widget-container").length) {
                            if (jQuery(this).is(":visible")) {
                             jQuery(".elementor-edit-mode  .elementor-widget-container .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css("display", "flex");
                            }
                          }
                          if (jQuery(".bricks-is-frontend").length) {
                            if (jQuery(this).is(":visible")) {
                               jQuery(".bricks-is-frontend .wpvr-cardboard .pnlm-container #sccontrols' . $pano_suffix . '").css({
                                    "display": "flex",
                                    "justify-content": "center",
                                    "align-items": "center"
                                });
                            }
                          }                          
                       });
                        jQuery("#' . $panoid . ' .wpvr_slider_nav").slideToggle();
                      }
              });';
            }
            $html .= 'panoshow' . $pano_suffix . '.on("load", function (){
                    jQuery("#controls' . $pano_suffix . '").show();
                    jQuery("#zoom-in-out-controls' . $pano_suffix . '").show();
                    jQuery("#adcontrol' . $pano_suffix . '").show();
                    jQuery("#explainer_button_' . $pano_suffix . '").show();
                    jQuery("#generic_form_button_' . $pano_suffix . '").show();
                    jQuery("#floor_map_button_' . $pano_suffix . '").show();
                    jQuery("#vrgcontrols' . $pano_suffix . '").show();
                    jQuery("#cp-logo-controls").show();
                    jQuery(".custom-scene-navigation").show();
                    jQuery("#' . $panoid . '").find(".pnlm-panorama-info").show();
            });';
        }

        //==Old code working properly==//

        $previeword = "Click to Load Panorama";
        if (isset($postdata['previewtext']) && $postdata['previewtext'] != '') {
            $previeword = $postdata['previewtext'];
        }
        $html .= 'jQuery(".elementor-tab-title").click(function(){
                      var element_id;
                      var pano_id;
                      var element_id = this.id;
                      element_id = element_id.split("-");
                      element_id = element_id[3];
                      jQuery("#elementor-tab-content-"+element_id).find("#' . $master_container_id . '").children("div").eq(1).addClass("awwww");
                      var pano_id = jQuery(".awwww").attr("id");
                      jQuery("#elementor-tab-content-"+element_id).find("#' . $master_container_id . '").children("div").eq(1).removeClass("awwww");;
                      if (pano_id != undefined) {
                        if (pano_id == "' . $panoid . '") {
                          jQuery("#' . $panoid . '").children(".pnlm-render-container").remove();
                          jQuery("#' . $panoid . '").children(".pnlm-ui").remove();
                          panoshow' . $pano_suffix . ' = pannellum.viewer(response[0]["panoid"], scenes);
                          window.wpvrViewers = window.wpvrViewers || {};
                          window.wpvrViewers[response[0]["panoid"]] = panoshow' . $pano_suffix . ';
                          document.dispatchEvent(new CustomEvent("wpvr:viewer-ready", {
                              detail: { containerId: response[0]["panoid"], viewer: panoshow' . $pano_suffix . ' }
                          }));
                          jQuery("#' . $panoid . '").children(".pnlm-ui").find(".pnlm-load-button p").text(' . wp_json_encode( (string) $previeword ) . ')
                          setTimeout(function() {
                                //   panoshow' . $pano_suffix . '.loadScene("' . $default_scene . '");
                                  window.dispatchEvent(new Event("resize"));
                                  if (jQuery("#' . $panoid . '").children().children(".pnlm-panorama-info:visible").length > 0) {
                                       jQuery("#controls' . $pano_suffix . '").css("bottom", "55px");
                                   } else {
                                     jQuery("#controls' . $pano_suffix . '").css("bottom", "5px");
                                   }
                          }, 200);
                        }
                      }
            });';
        $html .= 'jQuery(".geodir-tab-head dd, #vr-tour-tab").click(function(){
              jQuery("#' . $panoid . '").children(".pnlm-render-container").remove();
              jQuery("#' . $panoid . '").children(".pnlm-ui").remove();
              panoshow' . $pano_suffix . ' = pannellum.viewer(response[0]["panoid"], scenes);
              window.wpvrViewers = window.wpvrViewers || {};
              window.wpvrViewers[response[0]["panoid"]] = panoshow' . $pano_suffix . ';
              document.dispatchEvent(new CustomEvent("wpvr:viewer-ready", {
                  detail: { containerId: response[0]["panoid"], viewer: panoshow' . $pano_suffix . ' }
              }));
              setTimeout(function() {
                      panoshow' . $pano_suffix . '.loadScene(' . wp_json_encode( (string) $default_scene ) . ');
                      window.dispatchEvent(new Event("resize"));
                      if (jQuery("#' . $panoid . '").children().children(".pnlm-panorama-info:visible").length > 0) {
                           jQuery("#controls' . $pano_suffix . '").css("bottom", "55px");
                       }
                       else {
                         jQuery("#controls' . $pano_suffix . '").css("bottom", "5px");
                       }
              }, 200);
            });';
        if (isset($previeword) && $previeword != '') {
            $html .= '
            jQuery("#' . $panoid . '").children(".pnlm-ui").find(".pnlm-load-button p").text(' . wp_json_encode( (string) $previeword ) . ')
            ';
        }
        if ($default_global_zoom != '' || $max_global_zoom != '' || $min_global_zoom != '') {
            $html .= 'jQuery(".globalzoom").val("on").change();';
        }

        $html .= 'jQuery("#' . $panoid . ' .pnlm-title-box").on("mouseenter", function(){
                jQuery(this).attr("title", jQuery(this).text());
            });
            jQuery("#' . $panoid . ' .pnlm-title-box").on("mouseleave", function(){
                jQuery(this).removeAttr("title");
            });';
        $html .= '});';
        $html .= '})(jQuery);';
        $html .= '</script>';
        $tour_data = [];
        if(defined("WPVR_PRO_VERSION")){
            $tour_data = array(
                'explainerControlSwitch' => (
                    isset( $postdata['explainerSwitch'] )
                    && in_array( $postdata['explainerSwitch'], array( true, 1, '1', 'on' ), true )
                ) ? 'on' : 'off',
                'floor_plan_enable' => $floor_plan_enable ?? '',
                'floor_plan_image' => $floor_plan_image ?? '',
                'custom_control' => $custom_control ?? '',
            );
        }
        ob_start();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html;
        $output = ob_get_clean();
        return apply_filters('wpvr_generate_tour_layout_html', $output ,$postdata ,$id, $tour_data);
    }

    function replace_callback($matches){
        foreach ($matches as $match){
            return str_replace('<img','<img decoding="async"',$match);
        }

    }

private function sanitize_content_preserve_styles($content, $allow_forms = false) {
    // Decode HTML entities first (in case content was encoded in database)
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Escape or strip <script> blocks
    if ($allow_forms) {
        $content = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $content);
    } else {
        $content = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/si', function($matches) {
            return esc_html($matches[0]); // Convert to plain text
        }, $content);
    }

    // Strip dangerous URL-based attributes
    $content = preg_replace('/(href|action|formaction)\s*=\s*["\']?\s*(javascript|vbscript|data|about):/i', '$1=""', $content);

    // Escape inline event handlers (onclick, onhover, etc.) to display as text
    $content = preg_replace_callback('/\s*(on\w+)\s*=\s*(["\'])([^"\']*)\2/i', function($matches) {
        // Escape the attribute value but keep the attribute name visible as text
        return ' ' . esc_html($matches[1]) . '=' . $matches[2] . esc_html($matches[3]) . $matches[2];
    }, $content);
    $content = preg_replace_callback('/\s*(on\w+)\s*=\s*([^>\s]+)/i', function($matches) {
        // Handle unquoted event handlers
        return ' ' . esc_html($matches[1]) . '=' . esc_html($matches[2]);
    }, $content);

    // Remove unsafe embedded/interactive elements
    if ($allow_forms) {
        $content = preg_replace('/<(object|embed|applet|frame|frameset|meta|link|base)\b[^>]*>/i', '', $content);
        $content = preg_replace('/<\/(object|embed|applet|frame|frameset|meta|link|base)>/i', '', $content);
    } else {
        $content = preg_replace('/<(object|embed|applet|frame|frameset|meta|link|base|form|input|button|textarea|select|option)\b[^>]*>/i', '', $content);
        $content = preg_replace('/<\/(object|embed|applet|frame|frameset|meta|link|base|form|input|button|textarea|select|option)>/i', '', $content);
    }

    // Clean style attributes safely
    $content = preg_replace_callback('/style\s*=\s*["\']([^"\']*)["\']/', function($matches) {
        $style = $matches[1];
        $style = preg_replace('/expression\s*\(/i', '', $style);
        $style = preg_replace('/(javascript|vbscript|data|about)\s*:/i', '', $style);
        $style = preg_replace('/url\s*\(\s*["\']?\s*(javascript|vbscript|data):/i', '', $style);
        $style = preg_replace('/behavior\s*:/i', '', $style);
        $style = preg_replace('/-moz-binding\s*:/i', '', $style);
        return 'style="' . esc_attr($style) . '"';
    }, $content);

    // Sanitize <style> blocks
    $content = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/si', function($matches) {
        $css = $matches[1];
        $css = preg_replace('/(expression|javascript|vbscript|data|about)\s*:/i', '', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*(javascript|vbscript|data):/i', '', $css);
        $css = preg_replace('/behavior\s*:/i', '', $css);
        $css = preg_replace('/-moz-binding\s*:/i', '', $css);
        return '<style>' . esc_html($css) . '</style>';
    }, $content);

    // Allow iframes and styles from safe sources only
    $allowed_tags = wp_kses_allowed_html('post');
    $allowed_tags['style'] = [
        'type'  => true,
        'id'    => true,
        'class' => true,
        'media' => true,
    ];
    $allowed_tags['iframe'] = [
        'src'             => true,
        'width'           => true,
        'height'          => true,
        'frameborder'     => true,
        'allowfullscreen' => true,
        'class'           => true,
        'style'           => true,
        'title'           => true,
        'allow'           => true,
        'name'            => true,
        'referrerpolicy'  => true,
        'loading'         => true,
        'sandbox'         => true,
    ];
    $allowed_tags['img'] = [
        'src'      => true,
        'alt'      => true,
        'title'    => true,
        'width'    => true,
        'height'   => true,
        'class'    => true,
        'id'       => true,
        'style'    => true,
        'loading'  => true,
        'srcset'   => true,
        'sizes'    => true,
    ];

    if ($allow_forms) {
        $form_attributes = [
            'id'                 => true,
            'class'              => true,
            'style'              => true,
            'name'               => true,
            'value'              => true,
            'type'               => true,
            'placeholder'        => true,
            'action'             => true,
            'method'             => true,
            'target'             => true,
            'enctype'            => true,
            'disabled'           => true,
            'readonly'           => true,
            'required'           => true,
            'checked'            => true,
            'selected'           => true,
            'multiple'           => true,
            'size'               => true,
            'rows'               => true,
            'cols'               => true,
            'maxlength'          => true,
            'minlength'          => true,
            'min'                => true,
            'max'                => true,
            'step'               => true,
            'pattern'            => true,
            'autocomplete'       => true,
            'autofocus'          => true,
            'for'                => true,
            'data-*'             => true,
            'data-form_id'       => true,
            'data-form_instance' => true,
            'data-name'          => true,
            'data-type'          => true,
            'aria-invalid'       => true,
            'aria-required'      => true,
            'aria-label'         => true,
            'aria-describedby'   => true,
            'aria-labelledby'    => true,
        ];
        $allowed_tags['form']     = $form_attributes;
        $allowed_tags['input']    = $form_attributes;
        $allowed_tags['button']   = $form_attributes;
        $allowed_tags['textarea'] = $form_attributes;
        $allowed_tags['select']   = $form_attributes;
        $allowed_tags['option']   = $form_attributes;
        $allowed_tags['optgroup'] = $form_attributes;
        $allowed_tags['label']    = $form_attributes;
        $allowed_tags['fieldset'] = $form_attributes;
        $allowed_tags['legend']   = $form_attributes;
        if (!isset($allowed_tags['div'])) {
            $allowed_tags['div'] = [];
        }
        $allowed_tags['div']['data-*']             = true;
        $allowed_tags['div']['data-form_id']       = true;
        $allowed_tags['div']['data-form_instance'] = true;
        if (!isset($allowed_tags['span'])) {
            $allowed_tags['span'] = [];
        }
        $allowed_tags['span']['data-*']            = true;
    }

    // Apply wp_kses() to keep only allowed tags/attributes
    $content = wp_kses($content, $allowed_tags);

    // Finally, validate iframe src for security (allow https only, block javascript: etc.)
    $content = preg_replace_callback('/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>(?:<\/iframe>)?/i', function($matches) {
        $src = $matches[1];
        // Allow any https:// or http:// URL, but block javascript:, data:, vbscript:, etc.
        if (preg_match('/^(https?:)?\/\//i', $src) && !preg_match('/^(javascript|data|vbscript|about):/i', $src)) {
            return $matches[0]; // keep safe iframe
        }
        // Strip unsafe iframe
        return '';
    }, $content);

    return $content;
}

private function normalize_hotspot_external_url($url) {
    $url = trim((string) $url);

    if (
        $url === '' ||
        preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) ||
        preg_match('/^[\/#?]/', $url)
    ) {
        return $url;
    }

    if (preg_match('/^[^\s\/]+\.[^\s\/]+(?:[\/?#]|$)/i', $url)) {
        return 'https://' . $url;
    }

    return $url;
}

}
