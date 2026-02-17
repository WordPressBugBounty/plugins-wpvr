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
      // opt-in toggle ajax
      add_action( 'wp_ajax_wpvr_save_opt_in_toggle', array($this, 'wpvr_save_opt_in_toggle' ) );

      // Setup wizard specific AJAX handlers
      add_action( 'wp_ajax_wpvr_fetch_template', array($this, 'wpvr_fetch_template' ) );
      add_action( 'wp_ajax_wpvr_upload_image', array($this, 'wpvr_upload_image' ) );
      add_action( 'wp_ajax_wpvr_create_tour_from_wizard', array($this, 'wpvr_create_tour_from_wizard' ) );
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
    /**
	 * Verify current user has permission to perform this action.
	 *
	 * @return void
	 */
    if ( ! current_user_can('edit_posts') ) {
		wp_send_json([
			'success' => false,
			'data'    => 'Permission denied.'
		]);
	}

    /**
	 * Validate AJAX nonce to prevent unauthorized or forged requests.
	 *
	 * @return void
	 */
    $nonce  = sanitize_text_field($_POST['nonce']);
	if ( ! wp_verify_nonce( $nonce, 'wpvr' ) ) {
		wp_send_json([
			'success' => false,
			'data'    => 'Invalid or expired request.',
		]);
	}


    $postid = absint(sanitize_text_field($_POST['postid']) ?? 0);

	/**
	 * Ensures a valid post ID is supplied before proceeding.
	 *
	 * @return void
	 */
    if($postid < 1) {
		wp_send_json_error([
            'success' => false,
            'data' => '<span class="pano-error-title">Invalid post ID</span> <p>Malformed data passed.</p>'
		]);
        die();
    }

    /**
	 * Ensures the post type is 'wpvr_item' before proceeding.
	 *
	 * @return void
	 */
    $post_type = get_post_type( $postid );
    if ($post_type != 'wpvr_item') {
      die();
    }

    $panoid = 'pano' . $postid;

    /**
	 * Checks if this is a publish action and validates scene/video data.
	 *
	 * @return void
	 */
    $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'auto-draft';
    $is_publish_action = ($action_type === 'publish');

    /**
     * Checks if title is provided FIRST before any other validation.
     *
     * @return void
     */
    if ($is_publish_action && (!isset($_POST['post_title']) || empty(trim($_POST['post_title'])))) {
      wp_send_json([
        'success' => false,
        'data' => '<span class="pano-error-title">Title Required!</span> <p>Please provide a title for this tour before publishing.</p>'
      ]);
      die();
    }

    /**
	 * Validates scene/video data before allowing publication.
	 *
	 * @return void
	 */
    if ($is_publish_action) {

      $has_scene_data = false;
      $has_video_data = false;
      $is_video_mode = false;
      $is_street_view_mode = false;
      $has_street_view_data = false;

      // Check if video mode is enabled
      if (isset($_POST['panovideo']) && $_POST['panovideo'] === 'on') {
        $is_video_mode = true;
        if (isset($_POST['videourl']) && !empty($_POST['videourl'])) {
          $has_video_data = true;
        }
      } elseif (!empty($_POST['streetview']) && $_POST['streetview'] == 'on') {
        // Check if Street View mode is enabled (Pro feature)
        $is_street_view_mode = true;
          if (!empty($_POST['streetviewurl'])) {
              $has_street_view_data = true;
          }
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
      } elseif($is_street_view_mode && !$has_street_view_data) {
          $response = array(
              'success' => false,
              'data' => '<span class="pano-error-title">No Street View Data Found!</span> <p>Please add a street view URL in the street view settings before publishing this tour.</p>'
          );
          wp_send_json($response);
          die();
      }elseif (!$is_video_mode && !$is_street_view_mode && !$has_scene_data) {
        // Scene mode but no valid scenes found (exclude Street View from this check)
        $response = array(
          'success' => false,
          'data' => '<span class="pano-error-title">No Scene Data Found!</span> <p>Please add at least one scene with an image before publishing this tour.</p>'
        );
        wp_send_json($response);
        die();
      }
    }

    $post_array = array(
		'post_status'   => get_post_status( $postid ),
		'post_password' => get_post_field( 'post_password', $postid ),
		'visibility'    => 'public',
	);

	if ( isset( $_POST['post_status'] ) ) {
		$post_status               = sanitize_text_field( $_POST['post_status'] );
		$post_array['post_status'] = $post_status;
	}
	if ( isset( $_POST['post_password'] ) ) {
		$post_password               = sanitize_text_field( $_POST['post_password'] );
		$post_array['post_password'] = $post_password;
	}
	if ( isset( $_POST['visibility'] ) ) {
		$visibility               = sanitize_text_field( $_POST['visibility'] );
		$post_array['visibility'] = $visibility;
		if ( $visibility == 'public' || $visibility == 'private' ) {
			$post_array['post_password'] = '';
		}
	}

	if ( $post_array['visibility'] == 'private' ) {
		$post_array['post_status'] = 'private';
	} elseif ( $is_publish_action ) {
		$post_array['post_status'] = 'publish';
	} else {
		// Keep current status or set to draft if it's auto-draft
		$current_status = get_post_status( $postid );
		if ( $current_status === 'auto-draft' ) {
			$post_array['post_status'] = 'draft';
		}
	}

	$post_title = isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : get_the_title( $postid );
	wp_update_post( array(
		'ID'            => $postid,
		'post_status'   => $post_array['post_status'],
		'post_password' => $post_array['post_password'],
		'post_title'    => $post_title,
	) );

	do_action( 'wpvr_pro_update_street_view', $postid, $panoid );

	if ( isset( $_POST['checklistData'] ) && !empty( $_POST['checklistData'] ) ) {
		$checklist_data = array_map( 'sanitize_text_field', $_POST['checklistData'] );
		update_post_meta( $postid, 'wpvr_checklist', $checklist_data );
	}

	if ( isset( $_POST['panovideo'] ) && $_POST['panovideo'] == 'on' ) {
		$this->video->wpvr_update_meta_box( $postid, $panoid, $is_publish_action );
	} else {
		$this->scene->wpvr_update_meta_box( $postid, $panoid, $is_publish_action );
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


  /**
   * AJAX handler to persist opt-in toggle value
   * 
   */
  public function wpvr_save_opt_in_toggle() {
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
      return;
    }

    $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
    if ( !wp_verify_nonce( $nonce, 'wpvr' ) ) {
      wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
      return;
    }

    $opt_in = isset($_POST['opt_in']) ? sanitize_text_field($_POST['opt_in']) : '0';
    update_option('wpvr_opt_in_toggle', $opt_in);
    update_option('wpvr_allow_tracking', '1' === $opt_in  ? 'yes' : 'no');
    wp_send_json_success( array( 'message' => __('Opt-in value saved.', 'wpvr') ), 200 );
  }


  /**
   * Fetch template tour object from remote API
   * 
   * @since 8.5.48
   */
  public function wpvr_fetch_template() {
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
      return;
    }

    $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
    if ( !wp_verify_nonce( $nonce, 'wpvr' ) ) {
      wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
      return;
    }

    $industry = isset($_POST['industry']) ? sanitize_text_field($_POST['industry']) : 'real-estate';

    // Static industry to remote tour ID mapping
    $industry_id_map = array(
      'exhibitions'  => 2140,
      'offices'      => 2145,
      'real-estate'  => 2147,
      'hotel'        => 2149,
      'ecommerce'    => 2151,
      'showrooms'    => 2153,
      'school'       => 2155,
    );

    // Get source tour ID for the selected industry
    $source_tour_id = isset($industry_id_map[$industry]) ? $industry_id_map[$industry] : 2147;

    // Build API URL with source tour ID
    $api_url = 'https://showcase.rextheme.com/wp-json/wpvr/v1/tour/' . intval($source_tour_id);
    $api_url = apply_filters('wpvr_template_api_url', $api_url, $industry, $source_tour_id);
    $response = wp_remote_get($api_url, array(
      'timeout' => 30,
      'headers' => array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ),
    ));

    if ( is_wp_error( $response ) ) {
      wp_send_json_error( array( 'message' => 'Failed to fetch template: ' . $response->get_error_message() ) );
      return;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    if ( $status_code !== 200 ) {
      wp_send_json_error( array( 'message' => 'Template not found (HTTP ' . $status_code . ')' ) );
      return;
    }

    $body = wp_remote_retrieve_body( $response );
    $api_data = json_decode( $body, true );

    if ( ! $api_data || ! is_array( $api_data ) ) {
      wp_send_json_error( array( 'message' => 'Invalid template data received' ) );
      return;
    }

    $remote_meta = array();
    if ( isset( $api_data['meta_data'] ) && is_array( $api_data['meta_data'] ) ) {
      $remote_meta = $api_data['meta_data'];
    } elseif ( isset( $api_data['meta'] ) && is_array( $api_data['meta'] ) ) {
      $remote_meta = $api_data['meta'];
    }

    $panodata = array();
    if ( isset( $remote_meta['panodata'] ) ) {
      $panodata = $this->wpvr_normalize_panodata( $remote_meta['panodata'] );
    }
    if ( empty( $panodata ) && isset( $api_data['panodata'] ) ) {
      $panodata = $this->wpvr_normalize_panodata( $api_data['panodata'] );
    }

    if ( empty( $panodata ) ) {
      wp_send_json_error( array( 'message' => 'Template panodata missing in API response' ) );
      return;
    }

    $title = isset( $api_data['title'] ) && ! empty( $api_data['title'] )
      ? sanitize_text_field( $api_data['title'] )
      : 'My Virtual Tour';

    $post_data = array(
      'post_title'   => $title,
      'post_status'  => 'publish',
      'post_type'    => 'wpvr_item',
      'post_author'  => get_current_user_id(),
    );

    $post_id = wp_insert_post( $post_data );
    if ( is_wp_error( $post_id ) ) {
      wp_send_json_error( array( 'message' => 'Failed to create tour: ' . $post_id->get_error_message() ) );
      return;
    }

    $panodata = $this->wpvr_import_scene_attachments_to_media( $panodata, $post_id );
    $panodata['panoid'] = 'pano' . $post_id;

    // Keep meta panodata in sync with imported local scene URLs
    if ( ! is_array( $remote_meta ) ) {
      $remote_meta = array();
    }
    $remote_meta['panodata'] = $panodata;

    update_post_meta( $post_id, 'panodata', $panodata );
    update_post_meta( $post_id, 'wpvr_created_from_wizard', true );
    update_post_meta( $post_id, 'wpvr_wizard_industry', $industry );

    if ( ! empty( $remote_meta ) ) {
      foreach ( $remote_meta as $meta_key => $meta_value ) {
        $sanitized_key = sanitize_key( $meta_key );
        if ( empty( $sanitized_key ) || 'panodata' === $sanitized_key ) {
          continue;
        }

        if ( is_array( $meta_value ) ) {
          update_post_meta( $post_id, $sanitized_key, $meta_value );
        } else {
          update_post_meta( $post_id, $sanitized_key, sanitize_text_field( $meta_value ) );
        }
      }
    }

    $template_data = array(
      'industry' => $industry,
      'template_id' => $source_tour_id,
      'post_id' => $post_id,
      'edit_url' => admin_url( 'post.php?action=edit&post=' . $post_id ),
      'view_url' => get_permalink( $post_id ),
      'panodata' => $panodata,
      'meta' => $remote_meta,
    );

    if ( isset( $panodata['panodata']['scene-list']['1']['scene-attachment-url'] ) ) {
      $template_data['image_url'] = esc_url_raw( $panodata['panodata']['scene-list']['1']['scene-attachment-url'] );
    } elseif ( isset( $api_data['image_url'] ) ) {
      $template_data['image_url'] = esc_url_raw( $api_data['image_url'] );
    } elseif ( isset( $api_data['featured_image'] ) ) {
      $template_data['image_url'] = esc_url_raw( $api_data['featured_image'] );
    }

    do_action('rex_wpvr_tour_saved', $post_id);

    wp_send_json_success( array( 'template' => $template_data ) );
  }

  /**
   * Import scene attachment URLs into media library and replace URLs in panodata.
   *
   * @param array $panodata Panodata structure.
   * @param int   $post_id  Target post ID.
   *
   * @return array
   */
  private function wpvr_import_scene_attachments_to_media( $panodata, $post_id ) {
    if ( empty( $panodata['panodata']['scene-list'] ) || ! is_array( $panodata['panodata']['scene-list'] ) ) {
      return $panodata;
    }

    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $scene_image_keys = array(
      'scene-attachment-url',
      'scene-attachment-url-face0',
      'scene-attachment-url-face1',
      'scene-attachment-url-face2',
      'scene-attachment-url-face3',
      'scene-attachment-url-face4',
      'scene-attachment-url-face5',
    );

    foreach ( $panodata['panodata']['scene-list'] as $scene_key => $scene ) {
      if ( ! is_array( $scene ) ) {
        continue;
      }

      foreach ( $scene_image_keys as $image_key ) {
        if ( empty( $scene[ $image_key ] ) || ! is_string( $scene[ $image_key ] ) ) {
          continue;
        }

        $source_url = esc_url_raw( $scene[ $image_key ] );
        if ( empty( $source_url ) ) {
          continue;
        }

        $attachment_id = media_sideload_image( $source_url, $post_id, null, 'id' );
        if ( is_wp_error( $attachment_id ) ) {
          continue;
        }

        $local_url = wp_get_attachment_url( $attachment_id );
        if ( ! empty( $local_url ) ) {
          $panodata['panodata']['scene-list'][ $scene_key ][ $image_key ] = esc_url_raw( $local_url );
        }
      }
    }

    return $panodata;
  }

  /**
   * Normalize panodata payloads from array/serialized/json values.
   *
   * @param mixed $raw_panodata Panodata from remote API/meta.
   *
   * @return array
   */
  private function wpvr_normalize_panodata( $raw_panodata ) {
    if ( is_array( $raw_panodata ) ) {
      return $raw_panodata;
    }

    if ( is_string( $raw_panodata ) && '' !== $raw_panodata ) {
      $unserialized = maybe_unserialize( $raw_panodata );
      if ( is_array( $unserialized ) ) {
        return $unserialized;
      }

      $decoded_json = json_decode( $raw_panodata, true );
      if ( is_array( $decoded_json ) ) {
        return $decoded_json;
      }
    }

    return array();
  }

  /**
   * Upload image to WordPress media library
   * 
   * @since 8.5.48
   */
  public function wpvr_upload_image() {
    if ( ! current_user_can( 'upload_files' ) ) {
      wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
      return;
    }

    $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
    if ( !wp_verify_nonce( $nonce, 'wpvr' ) ) {
      wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
      return;
    }

    if ( ! isset( $_FILES['image'] ) || empty( $_FILES['image']['tmp_name'] ) ) {
      wp_send_json_error( array( 'message' => 'No file uploaded' ) );
      return;
    }

    // Validate file type
    $file_type = wp_check_filetype( $_FILES['image']['name'] );
    $allowed_types = array( 'jpg', 'jpeg', 'png', 'webp' );
    if ( ! in_array( strtolower( $file_type['ext'] ), $allowed_types ) ) {
      wp_send_json_error( array( 'message' => 'Invalid file type. Only JPG, PNG, and WEBP are allowed.' ) );
      return;
    }

    // Validate file size (max 50MB)
    if ( $_FILES['image']['size'] > 50 * 1024 * 1024 ) {
      wp_send_json_error( array( 'message' => 'File size must be less than 50MB' ) );
      return;
    }

    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $upload = wp_handle_upload( $_FILES['image'], array( 'test_form' => false ) );

    if ( isset( $upload['error'] ) ) {
      wp_send_json_error( array( 'message' => $upload['error'] ) );
      return;
    }

    $attachment = array(
      'post_mime_type' => $upload['type'],
      'post_title'     => sanitize_file_name( pathinfo( $_FILES['image']['name'], PATHINFO_FILENAME ) ),
      'post_content'  => '',
      'post_status'   => 'inherit'
    );

    $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    $image_url = wp_get_attachment_url( $attach_id );

    wp_send_json_success( array( 
      'attachment_id' => $attach_id,
      'url' => $image_url,
      'message' => 'Image uploaded successfully'
    ) );
  }

  /**
   * Create tour from wizard data
   * 
   * @since 8.5.48
   */
  public function wpvr_create_tour_from_wizard() {
    if ( ! current_user_can( 'edit_posts' ) ) {
      wp_send_json_error( array( 'message' => 'Unauthorized user' ), 403 );
      return;
    }

    $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
    if ( !wp_verify_nonce( $nonce, 'wpvr' ) ) {
      wp_send_json_error( array( 'message' => 'Invalid nonce' ), 400 );
      return;
    }

    $panodata = isset($_POST['panodata']) ? json_decode( stripslashes( $_POST['panodata'] ), true ) : array();
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'My Virtual Tour';
    $industry = isset($_POST['industry']) ? sanitize_text_field($_POST['industry']) : 'real-estate';
    $existing_post_id = isset($_POST['existing_post_id']) ? absint($_POST['existing_post_id']) : 0;

    if ( empty( $panodata ) ) {
      wp_send_json_error( array( 'message' => 'Panodata is required' ) );
      return;
    }

    if ( $existing_post_id > 0 ) {
      $existing_post = get_post( $existing_post_id );
      if ( ! $existing_post || 'wpvr_item' !== $existing_post->post_type || ! current_user_can( 'edit_post', $existing_post_id ) ) {
        wp_send_json_error( array( 'message' => 'Invalid existing tour ID' ) );
        return;
      }

      $post_id = $existing_post_id;
      wp_update_post(
        array(
          'ID' => $post_id,
          'post_title' => $title,
          'post_status' => 'publish',
        )
      );
    } else {
      // Create new post
      $post_data = array(
        'post_title'   => $title,
        'post_status'  => 'publish',
        'post_type'    => 'wpvr_item',
        'post_author'  => get_current_user_id(),
      );

      $post_id = wp_insert_post( $post_data );

      if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => 'Failed to create tour: ' . $post_id->get_error_message() ) );
        return;
      }
    }

    // Enforce local media URLs before final save/update
    $panodata = $this->wpvr_import_scene_attachments_to_media( $panodata, $post_id );

    // Set panoid as pano{post_id} in panodata
    $panodata['panoid'] = 'pano' . $post_id;

    // Save panodata as post meta
    update_post_meta( $post_id, 'panodata', $panodata );

    // Mark as created from wizard
    update_post_meta( $post_id, 'wpvr_created_from_wizard', true );
    update_post_meta( $post_id, 'wpvr_wizard_industry', $industry );

    // Save template meta fields if provided (dynamic meta from API)
    $template_meta = isset($_POST['templateMeta']) ? json_decode( stripslashes( $_POST['templateMeta'] ), true ) : array();
    if ( ! empty( $template_meta ) && is_array( $template_meta ) ) {
      foreach ( $template_meta as $meta_key => $meta_value ) {
        // Sanitize meta key to ensure it's a valid meta key
        $sanitized_key = sanitize_key( $meta_key );
        if ( ! empty( $sanitized_key ) && 'panodata' !== $sanitized_key ) {
          // Handle different value types
          if ( is_array( $meta_value ) ) {
            update_post_meta( $post_id, $sanitized_key, $meta_value );
          } else {
            update_post_meta( $post_id, $sanitized_key, sanitize_text_field( $meta_value ) );
          }
        }
      }
    }

    // Trigger tour saved action for telemetry
    do_action('rex_wpvr_tour_saved', $post_id);

    wp_send_json_success( array( 
      'post_id' => $post_id,
      'edit_url' => admin_url( 'post.php?action=edit&post=' . $post_id ),
      'view_url' => get_permalink( $post_id ),
      'message' => 'Tour created successfully'
    ) );
  }

}
