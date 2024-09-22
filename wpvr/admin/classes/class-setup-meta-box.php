<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Setup meta box related functionalities
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin/classes
 */

class WPVR_Setup_Meta_Box extends WPVR_Meta_Box {
  
	/**
	 * @var string
     * @since 8.0.0
	 */
	protected $title = '';
	
	/**
     * Metabox ID
     * 
	 * @var string
     * @since 8.0.0
	 */
	protected $slug = '';
	
	/**
	 * @var string
     * @since 8.0.0
	 */
	protected $post_type = '';
	
	/**
	 * Metabox context
     * 
     * @var string
     * @since 8.0.0
	 */
	protected $context = '';
	
	/**
	 * Metabox priority
     * 
     * @var string
     * @since 8.0.0
	 */
	protected $priority = '';

    /**
     * Instance of WPVR_Scene class
     * 
     * @var object
     * @since 8.0.0
     */
    protected $scene;

    /**
     * Instance of WPVR_Video class
     * 
     * @var object
     * @since 8.0.0
     */
    protected $video;

    /**
     * Instance of WPVR_General class
     * 
     * @var object
     * @since 8.0.0
     */
    protected $general;


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

        $this->scene             = new WPVR_Scene(); 
        $this->video             = new WPVR_Video(); 
        $this->general           = new WPVR_General();  
    
        add_action( 'add_meta_boxes', array( $this, 'register' ) );
    }


    /**
     * Register custom meta box
     * 
     * @param string $post_type
     * 
     * @return void
     * @since 8.0.0
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
     * @since 8.0.0
     */
    public function render( $post ) {

        $primary = WPVR_Meta_Field::get_primary_meta_fields();
        $post_meta_data = get_post_meta($post->ID, 'panodata', true);
        $post_meta_data = (is_array($post_meta_data)) ? $post_meta_data : array($post_meta_data);

        $postdata = array_merge($primary, $post_meta_data);
        // active tab variables
        $active_tab = 'scene';
        $scene_active_tab = 1;
        $hotspot_active_tab = 1;
        if (isset($_GET['active_tab'])) {
            $active_tab = sanitize_text_field($_GET['active_tab']);
        }
        if (isset($_GET['scene'])) {
            $scene_active_tab = sanitize_text_field($_GET['scene']);
        }
        if (isset($_GET['hotspot'])) {
            $hotspot_active_tab = sanitize_text_field($_GET['hotspot']);
        }

        // Start custom meta box rendering
        ob_start();

        ?>

        <div class="pano-setup">

            <input type="hidden" value="<?php echo esc_attr($active_tab);?>" name="wpvr_active_tab" id="wpvr_active_tab"/>
            <input type="hidden" value="<?php echo esc_attr($scene_active_tab);?>" name="wpvr_active_scenes" id="wpvr_active_scenes"/>
            <input type="hidden" value="<?php echo esc_attr($hotspot_active_tab);?>" name="wpvr_active_hotspot" id="wpvr_active_hotspot"/>                            

            <!-- start rex-pano-tabs -->
            <div class="rex-pano-tabs">
                
                <?php WPVR_Meta_Field::render_pano_tab_nav($postdata); ?>
                <!-- start rex-pano-tab-content -->
                <div class="rex-pano-tab-content" id="wpvr-main-tab-contents">

                    <!-- start scenes tab -->
                    <div class="rex-pano-tab wpvr_scene active" id="scenes">
                        <?php 
                            $this->scene->render_scene($postdata);
                        ?>
                    </div>
                    
                    <!-- start general tab -->
                    <div class="rex-pano-tab general" id="general">
                        <?php 
                            $this->general->render_setting($postdata);
                        ?>
                    </div>

                    <!-- start video tab content -->
                    <div class="rex-pano-tab video" id="video">
                        <?php 
                            $this->video->render_video($postdata); 
                        ?>
                    </div>

                    <!-- This hook will render floor plan tab content -->
                    <?php do_action( 'include_floor_plan_meta_content', $postdata )?>

                    <!-- This hook will render background Tour tab content -->
                    <?php do_action( 'include_background_tour_meta_content', $postdata )?>

                    <!-- This hook will render Street View tab content -->
                    <?php do_action( 'include_street_view_meta_content', $postdata )?>

                    <?php do_action( 'include_export_meta_content', $postdata, $post )?>

                </div>
                <!-- end rex-pano-tab-content -->
            </div>
            <!-- end rex-pano-tabs -->

            <div class="wpvr-help-resource">
                <div class="wpvr-help-resource__playlist">
                    <span class="wpvr-help-resource__video-section">
                        <svg class="wpvr-help-resource__video-icon" xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                            <path d="M11.2225 6.51521L11.2244 6.5163C11.59 6.73102 11.5918 7.26492 11.2235 7.4819L11.2227 7.4824L1.59806 13.1697L1.59703 13.1703C1.22418 13.3914 0.75 13.1232 0.75 12.6861V1.30884C0.75 1.03617 0.875311 0.890942 1.03011 0.814073C1.21377 0.722878 1.43862 0.731136 1.59792 0.825199C1.59797 0.825226 1.59802 0.825253 1.59806 0.82528L11.2225 6.51521Z" stroke="#73707D" stroke-width="1.5"/>
                        </svg>
                        <p class="wpvr-help-resource__video-title"><?php esc_html_e('Video', 'wpvr') ?></p>
                    </span>
                </div>

                <div class="wpvr-help-resource__dropdown">
                    <div class="wpvr-help-resource__dropdown-section" id="wpvr-help-resource__dropdown-section">
                        <svg width="12" height="18" fill="none" viewBox="0 0 12 18" class="wpvr-help-resource__dropdown-icon">
                            <path fill="#7A8B9A" d="M5.438 13.604a.646.646 0 01-.646-.647c0-.846.12-1.576.358-2.19.176-.464.46-.93.851-1.402.288-.344.804-.845 1.55-1.503.747-.659 1.232-1.183 1.456-1.574a2.53 2.53 0 00.335-1.282c0-.839-.328-1.574-.982-2.209-.654-.635-1.456-.952-2.407-.952-.918 0-1.685.288-2.299.863-.449.42-.786 1.013-1.01 1.779a1.121 1.121 0 01-1.209.796A1.122 1.122 0 01.49 3.854c.302-1.023.808-1.848 1.518-2.474C2.993.507 4.296.072 5.918.072c1.715 0 3.084.467 4.107 1.401 1.022.935 1.532 2.064 1.532 3.39 0 .766-.18 1.473-.539 2.118-.358.647-1.062 1.433-2.107 2.36-.702.622-1.161 1.082-1.377 1.377a3.05 3.05 0 00-.48 1.018c-.076.284-.129.702-.158 1.25a.652.652 0 01-.65.617h-.808v.001zM4.672 16.7a1.228 1.228 0 112.455 0 1.228 1.228 0 01-2.455 0z"></path>
                        </svg>
                    </div>

                    <ul class="wpvr-help-resource__dropdown-list" id="wpvr-help-resource__dropdown-list">
                        <li class="wpvr-help-resource__dropdown-item"><a target="_blank" href="https://rextheme.com/docs-category/wp-vr/" class="wpvr-help-resource__dropdown-link">Documentation</a></li>
                        <!-- <li class="wpvr-help-resource__dropdown-item"><a target="_blank" href="#" class="wpvr-help-resource__dropdown-link">Social Group</a></li> -->
                        <li class="wpvr-help-resource__dropdown-item"><a target="_blank" href="https://www.youtube.com/watch?v=aNwB066MYko&list=PLelDqLncNWcUndi1NkXJh2BH62OYmIayt&ab_channel=RexTheme" class="wpvr-help-resource__dropdown-link">YouTube Video</a></li>
                    </ul>
                </div>

            </div>

            <!-- End Help Resource -->

            <div class="wpvr-iframe-modal">
                <div class="wpvr-iframe-modal__content">
                    <span type="button" class="wpvr-iframe-modal__cross-icon">
                        <svg width="10" height="10" fill="none" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#686f7f" d="M.948 9.995a.94.94 0 01-.673-.23.966.966 0 010-1.352L8.317.278a.94.94 0 011.339.045c.323.35.342.887.044 1.258L1.611 9.765a.94.94 0 01-.663.23z"></path>
                            <path fill="#686f7f" d="M8.98 9.995a.942.942 0 01-.664-.278L.275 1.582A.966.966 0 01.378.23a.939.939 0 011.232 0L9.7 8.366a.966.966 0 010 1.399.94.94 0 01-.72.23z"></path>
                        </svg>
                    </span>

                    <iframe
                        class="video-link"
                        width="560"
                        height="315"
                        src="https://www.youtube.com/embed/kKHUlVKtesQ"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        id ="wpvr-help-video-iframe"
                        allowfullscreen>
                    </iframe>

                  
                </div>
            </div>


            
        </div>
        <div class="wpvr-loading" style="display:none;">Loading&#8230;</div>

        <?php
//        ob_end_flush();
        // End custom meta box rendering
    }    

}