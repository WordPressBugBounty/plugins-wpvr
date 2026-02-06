<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * The admin-specific Ajax files.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin
 */

class Wpvr_Ajax
{

  /**
   * Instance of WPVR_Format class
   * 
   * @var object
   * @since 8.0.0
   */
  protected $format;


  /**
   * Instance of WPVR_StreetView class
   * 
   * @var object
   * @since 8.0.0
   */
  protected $streetview;


  /**
   * Instance of WPVR_Video class
   * 
   * @var object
   * @since 8.0.0
   */
  protected $video;


  /**
   * Instance of WPVR_Scene class
   * 
   * @var object
   * @since 8.0.0
   */
  protected $scene;


  /**
   * Instance of WPVR_Validator class
   * 
   * @var object
   * @since 8.0.0
   */
  protected $validator;


  function __construct()
  {
    $this->format     = new WPVR_Format();
    $this->streetview = new WPVR_StreetView();
    $this->video      = new WPVR_Video();
    $this->scene      = new WPVR_Scene();
    $this->validator  = new WPVR_Validator();

    add_action('wp_ajax_wpvr_save',              array($this, 'wpvr_save_data'));
    add_action('wp_ajax_wpvr_preview',           array($this, 'wpvr_show_preview'));
    add_action('wp_ajax_wpvrstreetview_preview', array($this, 'wpvrstreetview_preview'));
    add_action('wp_ajax_wpvr_file_import',       array($this, 'wpvr_file_import'));
    add_action('wp_ajax_wpvr_role_management',   array($this, 'wpvr_role_management'));
    add_action('wp_ajax_wpvr_notice',            array($this, 'wpvr_notice'));
    add_action('wp_ajax_wpvr_dismiss_black_friday_notice',  array($this, 'dismiss_black_friday_notice'));
    add_action('wp_ajax_wpvr_review_request',  array($this, 'wpvr_review_request'));

    //setup wizard ajax
      add_action( 'wp_ajax_wpvr_create_contact', array($this, 'wpvr_create_contact' ) );

      //general setting ajax
      add_action( 'wp_ajax_wpvr_save_general_settings', array($this, 'wpvr_save_general_settings' ) );
  }

  public function wpvr_review_request()
  {
      if( !current_user_can( 'manage_options' ) ){
          wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
          return;
      }
    $nonce  = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'wpvr-dismiss-notice-five-star-review')) {
      $response = array(
        'success' => false,
        'data' => 'Permission denied.'
      );
      wp_send_json($response);
    }
    $payload = !empty($_POST['payload']) ? $_POST['payload'] : array();
    $data = array(
      'show'      => !empty($payload['show']) ? $payload['show'] : '',
      'time'      => !empty($payload['frequency']) && 'never' !== $payload['frequency'] ? time() : '',
      'frequency' => !empty($payload['frequency']) ? $payload['frequency'] : '',
    );
    update_option('wpvr_feed_review_request', $data);
    $response = array(
      'success' => true,
      'data' => 'Review request updated successfully.'
    );
    wp_send_json($response);
    die();
  }

  /**
   * Responsible for Tour Preview
   * 
   * @return void
   * @since 8.0.0
   */
  public function wpvr_show_preview()
  {
    //===Current user capabilities check===//
    if (!current_user_can('edit_posts')) {
      $response = array(
        'success'   => false,
        'data'  => 'Contact admin.'
      );
      wp_send_json($response);
    }
    //===Current user capabilities check===//
    //===Nonce check===//
    $nonce  = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'wpvr')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Nonce check===//

    $panoid = '';
    $postid = sanitize_text_field($_POST['postid']);
    $panoid = 'pano' . $postid;
    if (isset($_POST['panovideo'])) {
      $panovideo = sanitize_text_field($_POST['panovideo']);
    }

    $post_type = get_post_type($postid);
    if ($post_type != 'wpvr_item') {
      die();
    }

    do_action('wpvr_pro_street_view_preview', $postid, $panoid);

    if ($panovideo == 'off') {
      $this->scene->wpvr_scene_preview($panoid, $panovideo);     // Preapre preview based on Scene data //
    } else {
      $this->video->wpvr_video_preview($panoid);                 // Prepare preview based on Video data //
    }
  }


  /**
   * Responsible for saving WPVR data
   * 
   * @return void
   * @since 8.0.0
   */
  public function wpvr_save_data()
  {

    //===Current user capabilities check===//
    if (!current_user_can('edit_posts')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Current user capabilities check===//
    //===Nonce check===//
    $nonce  = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'wpvr')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Nonce check===//
    $panoid = '';
    $postid = sanitize_text_field($_POST['postid']);
    $post_type = get_post_type($postid);
    if ($post_type != 'wpvr_item') {
      die();
    }
    $panoid = 'pano' . $postid;

    // Check if this is a publish action and validate scene/video data
    $is_publish_action = false;
    if (isset($_POST['post_value'])) {
      $post_value = sanitize_text_field($_POST['post_value']);
      $user_site_language = get_locale();
      $language_mapping = [
          'ar' => ['نشر' => 'Publish', 'تحديث' => 'Update'],
          'pt_PT' => ['Publicar' => 'Publish', 'Atualizar' => 'Update'],
          'es_ES' => ['Publicar' => 'Publish', 'Actualizar' => 'Update'],
          'he_IL' => ['לפרסם' => 'Publish', 'לעדכן' => 'Update'],
          'af' => ['Publiseer' => 'Publish', 'Opdateer' => 'Update'],
          'cs_CZ' => ['Publikovat' => 'Publish', 'Aktualizovat' => 'Update'],
          'da_DK' => ['Udgiv' => 'Publish', 'Opdater' => 'Update'],
          'de_DE' => ['Veröffentlichen' => 'Publish', 'Aktualisieren' => 'Update'],
          'fi' => ['Julkaise' => 'Publish', 'Päivitä' => 'Update'],
          'hr' => ['Objavi' => 'Publish', 'Ažuriraj' => 'Update'],
          'it_IT' => ['Pubblica' => 'Publish', 'Aggiorna' => 'Update'],
          'ja' => ['公開' => 'Publish', '更新' => 'Update'],
          'nl_NL' => ['Publiceren' => 'Publish', 'Bijwerken' => 'Update'],
          'pl_PL' => ['Opublikuj' => 'Publish', 'Aktualizuj' => 'Update'],
          'ru_RU' => ['Опубликовать' => 'Publish', 'Обновить' => 'Update'],
          'sv_SE' => ['Publicera' => 'Publish', 'Uppdatera' => 'Update'],
          'fr_FR' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_CA' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_BE' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_CH' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_LU' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_MC' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_CM' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_DZ' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_MA' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_TN' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_SN' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_HT' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_RW' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_CD' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
          'fr_CI' => ['Publier' => 'Publish', 'Mettre à jour' => 'Update'],
      ];
      $post_value = $language_mapping[$user_site_language][$post_value] ?? $post_value;
      if ($post_value === 'Publish') {
        $is_publish_action = true;
      }
    }

    // Validate scene/video data before allowing publication
    if ($is_publish_action) {
      
      // Check if title is provided
      if (!isset($_POST['post_title']) || empty(trim($_POST['post_title']))) {
        $response = array(
          'success' => false,
          'data' => '<span class="pano-error-title">Title Required!</span> <p>Please provide a title for this tour before publishing.</p>'
        );
        wp_send_json($response);
        die();
      }
      
      $has_scene_data = false;
      $has_video_data = false;
      $is_video_mode = false;
      $is_street_view_mode = false;
      
      // Check if video mode is enabled
      if (isset($_POST['panovideo']) && $_POST['panovideo'] === 'on') {
        $is_video_mode = true;
        if (isset($_POST['videourl']) && !empty($_POST['videourl'])) {
          $has_video_data = true;
        }
      } elseif (isset($_POST['streetviewurl']) && !empty($_POST['streetviewurl'])) {
        // Check if Street View mode is enabled (Pro feature)
        $is_street_view_mode = true;
        // Street View doesn't require scene data as it uses Google Street View API
      } else {
        // Check for scene data
        if (isset($_POST['panodata']) && !empty($_POST['panodata'])) {
          $panodata = json_decode(stripslashes($_POST['panodata']), true);
          if (isset($panodata['scene-list']) && !empty($panodata['scene-list'])) {
            foreach ($panodata['scene-list'] as $scene) {
              // Check if it's a cubemap scene
              if (isset($scene['scene-type']) && $scene['scene-type'] === 'cubemap') {
                // Check all six faces of the cube
                $required_faces = array(
                  'scene-attachment-url-face0',
                  'scene-attachment-url-face1',
                  'scene-attachment-url-face2',
                  'scene-attachment-url-face3',
                  'scene-attachment-url-face4',
                  'scene-attachment-url-face5'
                );
                
                $missing_faces = array();
                foreach ($required_faces as $face) {
                  if (empty($scene[$face])) {
                    $missing_faces[] = $face;
                  }
                }
                
                if (!empty($missing_faces)) {
                  $response = array(
                    'success' => false,
                    'data' => '<span class="pano-error-title">Incomplete Cubemap Scene!</span> <p>Please add images for all six faces of the cube. Missing faces: ' . implode(', ', array_map(function($face) { return str_replace('scene-attachment-url-', '', $face); }, $missing_faces)) . '</p>'
                  );
                  wp_send_json($response);
                  die();
                }
                
                if (!empty($scene['scene-id'])) {
                  $has_scene_data = true;
                }
              } else {
                // Regular equirectangular scene check
                if (!empty($scene['scene-id']) && !empty($scene['scene-attachment-url'])) {
                  $has_scene_data = true;
                  break;
                }
              }
            }
          }
        }
      }
      
      // Provide specific error messages based on the mode and missing data
      if ($is_video_mode && !$has_video_data) {
        // Video mode is enabled but no video URL provided
        $response = array(
          'success' => false,
          'data' => '<span class="pano-error-title">No Video Data Found!</span> <p>Please add a video URL in the video settings before publishing this tour.</p>'
        );
        wp_send_json($response);
        die();
      } elseif (!$is_video_mode && !$is_street_view_mode && !$has_scene_data) {
        // Scene mode but no valid scenes found (exclude Street View from this check)
        $response = array(
          'success' => false,
          'data' => '<span class="pano-error-title">No Scene Data Found!</span> <p>Please add at least one scene with an image before publishing this tour.</p>'
        );
        wp_send_json($response);
        die();
      }
    }

    $post_status = get_post_status($postid);

    if ($post_status != 'publish') {
      wp_update_post(array(
        'ID' => $postid,
        'post_status' => 'publish'
      ));
    }


    $post_array = [];
    if (isset($_POST['post_status'])) {
      $post_status = sanitize_text_field($_POST['post_status']);
      $post_array['post_status'] = $post_status;
    }
    if (isset($_POST['post_password'])) {
      $visibility = sanitize_text_field($_POST['post_password']);
      $post_array['post_password'] = $visibility;
    }
    if (isset($_POST['visibility'])) {
      $visibility = sanitize_text_field($_POST['visibility']);
      $post_array['visibility'] = $visibility;
      if ($visibility == 'public' || $visibility == 'private') {
        $post_array['post_password'] = '';
      }
    }

      $user_site_language = get_locale();
      $language_mapping = [
          'ar' => [  // Arabic
              'نشر'    => 'Publish',   // nashr
              'تحديث'  => 'Update'     // tahdith
          ],
          'pt_PT' => [  // Portuguese (Portugal)
              'Publicar'   => 'Publish',
              'Atualizar'  => 'Update'
          ],
          'es_ES' => [  // Spanish (Spain)
              'Publicar'   => 'Publish',
              'Actualizar' => 'Update'
          ],
          'he_IL' => [  // Hebrew (Israel)
              'לפרסם'   => 'Publish',    // lefarsem
              'לעדכן'    => 'Update'      // le'adken
          ],
          'af' => [  // Afrikaans
              'Publiseer'  => 'Publish',
              'Opdateer'   => 'Update'
          ],
          'cs_CZ' => [  // Czech (Czech Republic)
              'Publikovat'  => 'Publish',
              'Aktualizovat'=> 'Update'
          ],
          'da_DK' => [  // Danish (Denmark)
              'Udgiv'       => 'Publish',
              'Opdater'     => 'Update'
          ],
          'de_DE' => [  // German (Germany)
              'Veröffentlichen' => 'Publish',
              'Aktualisieren'   => 'Update'
          ],
          'fi' => [  // Finnish
              'Julkaise'    => 'Publish',
              'Päivitä'     => 'Update'
          ],
          'hr' => [  // Croatian
              'Objavi'      => 'Publish',
              'Ažuriraj'    => 'Update'
          ],
          'it_IT' => [  // Italian (Italy)
              'Pubblica'    => 'Publish',
              'Aggiorna'    => 'Update'
          ],
          'ja' => [  // Japanese
              '公開'        => 'Publish',    // こうかい (Kōkai)
              '更新'        => 'Update'      // こうしん (Kōshin)
          ],
          'nl_NL' => [  // Dutch (Netherlands)
              'Publiceren'  => 'Publish',
              'Bijwerken'   => 'Update'
          ],
          'pl_PL' => [  // Polish (Poland)
              'Opublikuj'   => 'Publish',
              'Aktualizuj'  => 'Update'
          ],
          'ru_RU' => [  // Russian (Russia)
              'Опубликовать' => 'Publish',    // Opublikovat'
              'Обновить'       => 'Update'      // Obnovit'
          ],
          'sv_SE' => [  // Swedish (Sweden)
              'Publicera'   => 'Publish',
              'Uppdatera'   => 'Update'
          ],
          'fr_FR' => [  // French (France)
              'Publier'     => 'Publish',
              'Mettre à jour' => 'Update'
          ],
          'fr_CA' => [  // French (Canada)
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update', // or 'Actualiser'
          ],
          'fr_BE' => [  // French (Belgium)
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_CH' => [  // Switzerland
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_LU' => [  // Luxembourg
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_MC' => [  // Monaco
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_CM' => [  // Cameroon
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_DZ' => [  // Algeria
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_MA' => [  // Morocco
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_TN' => [  // Tunisia
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_SN' => [  // Senegal
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_HT' => [  // Haiti
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_RW' => [  // Rwanda
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_CD' => [  // DR Congo
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
          'fr_CI' => [  // Côte d’Ivoire
              'Publier'        => 'Publish',
              'Mettre à jour'  => 'Update',
          ],
      ];
      
    if (isset($_POST['post_value'])) {
      $post_value = sanitize_text_field($_POST['post_value']);
      $post_value = $language_mapping[$user_site_language][$post_value] ?? $post_value;
      if ($post_array['visibility'] == 'private') {
        $post_array['post_status'] = 'private';
      } elseif ($post_value === 'Publish') {
        $post_array['post_status'] = 'publish';
      }
    }
    $post_title = sanitize_text_field($_POST['post_title']);
    wp_update_post(array(
      'ID' => $postid,
      'post_status' => $post_array['post_status'],
      'post_password' => $post_array['post_password'],
      'visibility' => $post_array['visibility'],
      'post_title' => $post_title,

    ));

    do_action('wpvr_pro_update_street_view', $postid, $panoid);

    if(isset( $_POST['checklistData'] ) && !empty($_POST['checklistData'])){
        $checklist_data = array_map( 'sanitize_text_field', $_POST['checklistData'] );
        update_post_meta($postid, 'wpvr_checklist', $checklist_data );
    }

    if ($_POST['panovideo'] == "on") {
      $this->video->wpvr_update_meta_box($postid, $panoid);
    } else {
      $this->scene->wpvr_update_meta_box($postid, $panoid);
    }

    do_action('rex_wpvr_tour_saved', $postid);

    $response = array(
      'success'   => true,
      'data'  => array(
        'post_ID' => $postid,
        'post_status' => get_post_status($postid)
      )
    );
    wp_send_json($response);
    die();
  }


  /**
   * Responsible for importing tour
   * 
   * @return void
   * @since 8.0.0
   */
  public function wpvr_file_import()
  {
    //===Current user capabilities check===//
    if (!current_user_can('edit_posts')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Current user capabilities check===//
    //===Nonce check===//
    $nonce  = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'wpvr')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    $file_name = '';

      if ( isset( $_FILES['wpvr_import_file'] ) && ! empty( $_FILES['wpvr_import_file']['tmp_name'] ) ) {
          $file = $_FILES['wpvr_import_file'];

          // Validate file type - check if it's a ZIP file
          $file_type = wp_check_filetype($file['name']);
          $file_ext = strtolower($file_type['ext']);
          if ($file_ext !== 'zip') {
              wp_send_json_error(array('message' => 'Invalid file format. Only ZIP files are allowed.'));
              return;
          }

          // Get WordPress uploads directory
          $upload_dir = wp_upload_dir();
          $temp_folder = $upload_dir['basedir'] . '/wpvr_imported_temp';

          // Create temp folder if it doesn't exist
          if ( ! file_exists( $temp_folder ) ) {
              wp_mkdir_p( $temp_folder );
          }

          $file_name = basename( $file['name'] );

          // Define target file path inside temp folder
          $target_file = $temp_folder . '/' . basename( $file['name'] );

          move_uploaded_file( $file['tmp_name'], $target_file );

      } else {
          wp_send_json_error( array( 'message' => 'No file selected.' ) );
      }

    //===Nonce check===//
    WPVR_Import::prepare_tour_import_feature($file_name);
  }



  /**
   * WPVR Role Management
   * 
   * @return void
   * @since 8.0.0
   */
  function wpvr_role_management()
  {

    //===Current user capabilities check===//
    if (!current_user_can('manage_options')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Current user capabilities check===//
    //===Nonce check===//
    $nonce  = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'wpvr')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Nonce check===//

    $editor = sanitize_text_field($_POST['editor']);
    $author = sanitize_text_field($_POST['author']);
    $fontawesome = sanitize_text_field($_POST['fontawesome']);

  
    $cardboard = !empty($_POST['wpvr_cardboard_disable']) ? sanitize_text_field($_POST['wpvr_cardboard_disable']) : 'no'; //
  
    $wpvr_webp_conversion = !empty($_POST['wpvr_webp_conversion']) ? sanitize_text_field($_POST['wpvr_webp_conversion']) : 'no';

    $mobile_media_resize = sanitize_text_field($_POST['mobile_media_resize']);
    $high_res_image = sanitize_text_field($_POST['high_res_image']);
    $dis_on_hover = sanitize_text_field($_POST['dis_on_hover']);
    $wpvr_frontend_notice = sanitize_text_field($_POST['wpvr_frontend_notice']);
    $wpvr_frontend_notice_area = sanitize_text_field($_POST['wpvr_frontend_notice_area']);
    $wpvr_script_control = sanitize_text_field($_POST['wpvr_script_control']);
    $wpvr_script_list = sanitize_text_field($_POST['wpvr_script_list']);

    $wpvr_video_script_control = sanitize_text_field($_POST['wpvr_video_script_control']);
    $wpvr_video_script_list = sanitize_text_field($_POST['wpvr_video_script_list']);

    //        $enable_woocommerce = sanitize_text_field($_POST['woocommerce']);

    $wpvr_script_list = str_replace(' ', '', $wpvr_script_list);

    update_option('wpvr_editor_active', $editor);
    update_option('wpvr_author_active', $author);
    update_option('wpvr_fontawesome_disable', $fontawesome);
    update_option('wpvr_cardboard_disable', $cardboard);
    update_option('wpvr_webp_conversion', $wpvr_webp_conversion);
    update_option('mobile_media_resize', $mobile_media_resize);
    update_option('high_res_image', $high_res_image);
    update_option('dis_on_hover', $dis_on_hover);
    update_option('wpvr_frontend_notice', $wpvr_frontend_notice);
    update_option('wpvr_frontend_notice_area', $wpvr_frontend_notice_area);
    update_option('wpvr_script_control', $wpvr_script_control);
    update_option('wpvr_script_list', $wpvr_script_list);

    update_option('wpvr_video_script_control', $wpvr_video_script_control);
    update_option('wpvr_video_script_list', $wpvr_video_script_list);

    if(is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active( 'dokan-pro/dokan.php' )){
        $dokan_vendor = isset( $_POST['dokan_vendor'] ) ? sanitize_text_field($_POST['dokan_vendor']) : false;
        update_option('dokan_vendor_active', $dokan_vendor);
    }

    //        update_option('wpvr_enable_woocommerce', $enable_woocommerce);

    $response = array(
      'status' => 'success',
      'message' => 'Successfully saved',
    );
    wp_send_json($response);
  }


  /**
   * WPVR Notice
   * 
   * @return void
   * @since 8.0.0
   */
  function wpvr_notice()
  {
    //===Current user capabilities check===//
    if (!current_user_can('manage_options')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Current user capabilities check===//
    //===Nonce check===//
    $nonce  = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'wpvr')) {
      $response = array(
        'success'   => false,
        'data'  => 'Permission denied.'
      );
      wp_send_json($response);
    }
    //===Nonce check===//
    update_option('wpvr_black_friday_notice', '1');
  }

  /**
   * Dismiss black friday notice
   */
  function dismiss_black_friday_notice(){
      if( !current_user_can( 'manage_options' ) ){
          wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
          return;
      }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpvr')) {
      wp_die(__('Permission check failed', 'wpvr'));
    }
    update_option('_wpvr_eid_al_adha_2024', 'yes');
    echo json_encode(['success' => true,]);
    wp_die();
  }

/**
 * Handles the creation of a contact via a webhook.
 *
 * This function validates the nonce, sanitizes and validates the input fields,
 * and then creates a new contact using the WPVR_Create_Contact class.
 *
 * @since 8.4.10
 */
  function wpvr_create_contact(){
      if( !current_user_can( 'manage_options' ) ){
          wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
          return;
      }
      $nonce = filter_input(INPUT_POST, 'security', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $nonce = !empty( $nonce ) ? $nonce : null;
      if ( !wp_verify_nonce( $nonce, 'wpvr' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
            return;
        }

      $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $industry = filter_input(INPUT_POST, 'industry', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
      $opt_in = filter_input(INPUT_POST, 'opt_in', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

      $name = !empty($name) ? $name: '';
      $industry = !empty($industry ) ? $industry : '';
      $email = !empty( $email ) ? $email : '';

        if ( empty( $email ) ) {
          wp_send_json_error( array( 'message' => __('Email is required', 'rex-product-feed') ), 400 );
        }elseif( !is_email( $email ) ){
          wp_send_json_error( array( 'message' => __('Email is invalid', 'rex-product-feed') ), 400 );
        }

        $create_contact_instance = new WPVR_Create_Contact( $email, $name, $industry );
        $response = $create_contact_instance->create_contact_via_webhook();

        update_option('wpvr_posthog_access_enabled', $opt_in);


        if ( $response ) {
            wp_send_json_success( array( 'message' => __('Contact created successfully', 'wpvr') ), 200 );
        } else {
            wp_send_json_error( array( 'message' => __('Failed to create contact', 'wpvr') ), 500 );
        }
    }

    /**
     * Saves the general settings for the WPVR plugin.
     *
     * This function handles the nonce verification, sanitizes the input fields,
     * and updates the options in the database. It responds with a JSON success or error message.
     *
     * @since 8.4.10
     */
  function wpvr_save_general_settings(){

      if ( ! current_user_can( 'manage_options' ) ) {
          wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
          return;
      }

      $nonce = filter_input(INPUT_POST, 'security', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $nonce = !empty( $nonce ) ? $nonce : null; // phpcs:ignore
      if ( !wp_verify_nonce( $nonce, 'wpvr' ) ) {
          wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
          return;
      }

      $is_mobile_media_resize = filter_input(INPUT_POST, 'media_resizer', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $convert_to_webp = filter_input(INPUT_POST, 'convert_to_webp', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $vr_glass_support = filter_input(INPUT_POST, 'vr_glass_support', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

      update_option('mobile_media_resize', $is_mobile_media_resize);
      update_option('wpvr_webp_conversion', $convert_to_webp);
      update_option('wpvr_cardboard_disable', $vr_glass_support);

      wp_send_json_success( array( 'message' => __('General setting data successfully saved.', 'wpvr') ), 200 );
  }

}
