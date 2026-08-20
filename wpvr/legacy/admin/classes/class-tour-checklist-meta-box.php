<?php
/**
 * WPVR Tour Checklist Meta Box
 *
 * @package WPVR Admin Classes
 * @since 8.5.24
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * WPVR_Tour_Checklist_Meta_Box
 *
 * @since 8.5.24
 */
class WPVR_Tour_Checklist_Meta_Box extends WPVR_Meta_Box {
  
	/**
	 * @var string
     * @since 8.5.24
	 */
	protected $title = '';
	
	/**
     * Metabox ID
     * 
	 * @var string
     * @since 8.5.24
	 */
	protected $slug = '';
	
	/**
	 * @var string
     * @since 8.5.24
	 */
	protected $post_type = '';
	
	/**
	 * Metabox context
     * 
     * @var string
     * @since 8.5.24
	 */
	protected $context = '';
	
	/**
	 * Metabox priority
     * 
     * @var string
     * @since 8.5.24
	 */
	protected $priority = '';



    /**
     * Constructor
     *
     * @param string $slug Slug of the meta box
     * @param string $title Title of the meta box
     * @param string $post_type Post type of the meta box
     * @param string $context Context of the meta box
     * @param string $priority Priority of the meta box
     *
     * @return void
     * @since 8.5.24
     */
    public function __construct( $slug, $title, $post_type, $context, $priority ) {
        if( $slug == '' || $context == '' || $priority == '' )  {
            return;
        }
    
        if( $title == '' ) {
            $this->title = ucfirst( $slug );
        }
    
        if( empty( $post_type ) ) {
            return;
        }
    
        $this->title     = $title; 
        $this->slug      = $slug;
        $this->post_type = $post_type;
        $this->context   = $context;
        $this->priority  = $priority;

        add_action( 'add_meta_boxes', array( $this, 'register' ) );
    }


    /**
     * Register custom meta box
     * 
     * @param string $post_type
     * 
     * @return void
     * @since 8.5.24
     */
    public function register( $post_type ) {
        if ( $post_type == $this->post_type ) {
            add_meta_box( $this->slug, $this->title, array( $this, 'render' ), $post_type, $this->context, $this->priority );
        }
    }
    

    /**
     * Render custom meta box
     * 
     * @param object $post
     * 
     * @return void
     * @since 8.5.24
     */
    public function render( $post ) {
        $saved_checklist = get_post_meta($post->ID, 'wpvr_checklist', true);
        $pano_data = get_post_meta($post->ID, 'panodata', true);

        $wpvr_check_scene = isset($saved_checklist['wpvr_check_scene']) && $saved_checklist['wpvr_check_scene'] === 'true' ? true : false;
        $wpvr_check_media = isset($saved_checklist['wpvr_check_media']) && $saved_checklist['wpvr_check_media'] === 'true' ? true : false;
        $wpvr_check_default = isset($saved_checklist['wpvr_check_default']) && $saved_checklist['wpvr_check_default'] === 'true' ? true : false;
        $wpvr_check_hotspots = isset($saved_checklist['wpvr_check_hotspots']) && $saved_checklist['wpvr_check_hotspots'] === 'true' ? true : false;
        $wpvr_check_movement_controls = isset($saved_checklist['wpvr_check_movement-controls']) && $saved_checklist['wpvr_check_movement-controls'] === 'true' ? true : false;
        $wpvr_check_zoom_controls = isset($saved_checklist['wpvr_check_zoom-controls']) && $saved_checklist['wpvr_check_zoom-controls'] === 'true' ? true : false;
        $wpvr_check_publish = isset($saved_checklist['wpvr_check_publish']) && $saved_checklist['wpvr_check_publish'] === 'true' ? true : false;

        if (!$wpvr_check_scene) {
            $wpvr_check_scene = isset($pano_data['panodata']['scene-list']) && count($pano_data['panodata']['scene-list']) > 0;
        }

        if (!$wpvr_check_media) {
            $wpvr_check_media = $this->hasValidSceneAttachment($pano_data);
        }

        if (!$wpvr_check_default) {
            $wpvr_check_default = $this->hasDefaultScene($pano_data);
        }

        if (!$wpvr_check_publish) {
            $wpvr_check_publish = $this->getPostStatusById($post->ID);
        }

        if (!$wpvr_check_hotspots) {
            $wpvr_check_hotspots = $this->hasValidHotspot($pano_data);
        }

        require_once WPVR_PLUGIN_DIR_PATH . 'admin/partials/wpvr_checklist_template.php';
    }


    /**
     * Check if the tour has a valid scene attachment
     *
     * @param array $panodata
     *
     * @return bool
     * @since 8.5.24
     */
    public function hasValidSceneAttachment($panodata) {
        if (!isset($panodata['panodata']['scene-list']) || !is_array($panodata['panodata']['scene-list'])) {
            return false;
        }

        foreach ($panodata['panodata']['scene-list'] as $scene) {
            if (!empty($scene['scene-attachment-url'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the tour has a valid hotspot
     *
     * @param array $panodata
     *
     * @return bool
     * @since 8.5.24
     */
    public function hasValidHotspot($panodata) {
        if (!isset($panodata['panodata']['scene-list']) || !is_array($panodata['panodata']['scene-list'])) {
            return false;
        }

        foreach ($panodata['panodata']['scene-list'] as $scene) {
            if (isset($scene['hotspot-list']) && is_array($scene['hotspot-list']) && !empty($scene['hotspot-list'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the post is published
     *
     * @param int $post_id
     *
     * @return bool
     * @since 8.5.24
     */
    public function getPostStatusById($post_id) {
        $status = get_post_status($post_id);
        return $status === 'publish' ? true : false;
    }


    /**
     * Check if the tour has a default scene
     *
     * @param array $panodata
     *
     * @return bool
     * @since 8.5.24
     */
    public function hasDefaultScene($panodata){
        if (!isset($panodata['panodata']['scene-list']) || !is_array($panodata['panodata']['scene-list'])) {
            return false;
        }

        foreach ($panodata['panodata']['scene-list'] as $scene) {
            if (isset($scene['dscene']) && !empty($scene['dscene']) && 'on' === $scene['dscene']) {
                return true;
            }
        }

        return false;
    }

}