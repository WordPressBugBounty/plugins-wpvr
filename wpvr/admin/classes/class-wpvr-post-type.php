<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Define the custom post type
 *
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/includes/classes
 */


class WPVR_Post_Type {

    /**
     * The ID of this plugin.
     *
     * @since    8.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    8.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The post type of this plugin.
     *
     * @since 8.0.0
     */
    private $post_type;

    /**
     * Initialize the class and set its properties.
     *
     * @since    8.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version, $post_type) {
 
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->post_type = $post_type;

        // Register the post type
        add_action('init', array($this, 'register'));
 
        // Admin set post columns
        add_filter('manage_edit-' . $this->post_type . '_columns', array($this, 'set_columns')) ;

        // Set messages
        add_filter('post_updated_messages', array($this, 'wpvr_post_updated_messages') );
 
        // Admin edit post columns
        add_filter('manage_' . $this->post_type . '_posts_custom_column', array($this, 'edit_columns'));

        add_filter('admin_body_class',array($this, 'add_body_class'));

        add_filter('admin_head',array($this, 'add_javascript_in_wpvr_admin_page'));

        // add_action('admin_footer',array($this, 'add_new_tour_modal'));

 
    }

    /**
     * Adds a modal for creating a new tour.
     *
     * This function checks if the current screen is the post type edit screen and
     * if so, it displays a modal for creating a new tour. The modal includes options
     * for selecting the tour type, entering the tour name, and submitting the form.
     *
     * @return void
     */
    public function add_new_tour_modal()
    {
        $current_screen = get_current_screen();

        if(!empty($current_screen->post_type) && $current_screen->post_type == $this->post_type && $current_screen->base == 'edit'){

            $radio_icon = '<svg width="27" height="27" fill="none" viewBox="-3 -3 25 25" xmlns="http://www.w3.org/2000/svg"><rect width="19" height="19" x=".5" y=".5" fill="#fff" class="rect1" rx="9.5"/><rect width="19" height="19" x=".5" y=".5" stroke="#D8CCFF" class="rect2" rx="9.5"/><circle cx="10" cy="10" r="5" fill="#3F04FE"/></svg>';
            ?>
            <!-- start add new tour modal -->
            <div class="wpvr-create-tour-modal">
                <div class="wpvr-create-tour-inner">
                    <div class="wpvr-create-tour-modal-wrapper">
                        <div class="wpvr-create-tour-modal-header">
                            <h4 class="wpvr-create-tour-modal-title"><?php echo __('Create Tour', 'wpvr'); ?></h4>
                            <button type="button" class="wpvr-create-tour-modal-close" aria-label="Close modal">
                                <svg width="22" height="22" fill="none" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg"><g stroke="#B0AAC5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" clip-path="url(#clip0_874_3238)"><path d="M16.5 5.5l-11 11m0-11l11 11"/></g><defs><clipPath id="clip0_874_3238"><path fill="#fff" d="M0 0h22v22H0z"/></clipPath></defs></svg>
                            </button>
                        </div>

                        <div class="wpvr-create-tour-modal-content">
                            <div class="wpvr-modal-form-group tour-type-group">
                                <label class="field-label">
                                    <?php echo __('Select your preferred tour', 'wpvr'); ?>
                                </label>
                                
                                <div class="tour-type-wrapper">
                                    <label class="single-tour-type">
                                        <input type="radio" name="tour-type" value="image-tour" checked>
                                        <span class="radio-box">
                                            <?php echo $radio_icon; ?>

                                            <span class="radio-label">
                                                <?php echo __('360 Image Tour', 'wpvr'); ?>
                                            </span>
                                        </span>
                                    </label>

                                    <label class="single-tour-type">
                                        <input type="radio" name="tour-type" value="video-tour">
                                        <span class="radio-box">
                                            <?php echo $radio_icon; ?>

                                            <span class="radio-label">
                                                <?php echo __('360 Video Tour', 'wpvr'); ?>
                                            </span>
                                        </span>
                                    </label>

                                    <label class="single-tour-type <?php echo !defined('WPVR_PRO_VERSION') ? 'is-pro' : ''; ?>">

                                        <?php if(defined('WPVR_PRO_VERSION')){ ?>
                                            <input type="radio" name="tour-type" value="street-view-tour">
                                        <?php } ?>
                                        
                                        <span class="radio-box">
                                            <?php echo $radio_icon; ?>

                                            <span class="radio-label">
                                                <?php echo __('Street View', 'wpvr'); ?>
                                            </span>

                                            <?php if(!defined('WPVR_PRO_VERSION')){ ?>
                                                <span class="pro-tag"><?php echo __('pro', 'wpvr'); ?></span>
                                            <?php } ?>
                                        </span>
                                    </label>

                                    <label class="single-tour-type <?php echo !defined('WPVR_PRO_VERSION') ? 'is-pro' : ''; ?>">
                                        <?php if(defined('WPVR_PRO_VERSION')){ ?>
                                            <input type="radio" name="tour-type" value="bg-tour">
                                        <?php } ?>
                                        <span class="radio-box">
                                            <?php echo $radio_icon; ?>

                                            <span class="radio-label">
                                                <?php echo __('Background Tour', 'wpvr'); ?>
                                            </span>

                                            <?php if(!defined('WPVR_PRO_VERSION')){ ?>
                                                <span class="pro-tag"><?php echo __('pro', 'wpvr'); ?></span>
                                            <?php } ?>
                                        </span>
                                    </label>

                                </div>
                            </div>

                            <div class="wpvr-modal-form-group">
                                <label class="field-label">
                                    <?php echo __('Tour Name', 'wpvr'); ?>
                                </label>
                                <input type="text" class="wpvr-tour-name-input" placeholder="Enter name">
                            </div>
                        </div>

                        <div class="wpvr-create-tour-modal-footer">
                            <div class="modal-action-buttons">
                                <button type="button" class="wpvr-modal-btn modal-cancel" aria-label="Cancel modal"><?php echo __('Cancel', 'wpvr'); ?></button>

                                <button type="button" class="wpvr-modal-btn modal-submit" aria-label="Submit modal"><?php echo __('Create Tour', 'wpvr'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end add new tour modal -->
            <?php

        }
    }

    /**
     * Adds JavaScript functionality to the WPVR admin page.
     *
     * This function checks if the current screen is the WPVR admin page and if so, 
     * it adds JavaScript code to modify the page's behavior. The JavaScript code 
     * includes event listeners for various elements on the page, such as the 
     * page title action button, the create tour modal, and the tour type radio 
     * buttons. It also handles the creation of a new tour based on the selected 
     * tour type and title.
     *
     * @return void
     */
    public function add_javascript_in_wpvr_admin_page()
    {
        $current_screen = get_current_screen();

        if(!empty($current_screen->post_type) && $current_screen->post_type == $this->post_type && $current_screen->base == 'edit'){
            ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function () {
                    var searchInput = document.querySelector('#post-search-input');
                    if (searchInput) {
                        searchInput.placeholder = 'Search Tour...';
                    }
                });


                /**
                 * Adds event listeners to the wpvrPageTitleAction element.
                 * 
                 * When the wpvrPageTitleAction element is clicked, the following actions are performed:
                 * 1. Prevent the default behavior of the click event.
                 * 2. Show the createTourModal element.
                 * 3. Hide the createTourModal element when the tourCloseButton is clicked.
                 * 4. Hide the createTourModal element when the TourCancelButton is clicked.
                 * 5. Hide the createTourModal element when clicking outside of the modal-content but inside the modal.
                 * 6. Prevent closing the createTourModal element when clicking inside the createTourModalWrapper.
                */

                // document.addEventListener('DOMContentLoaded', function () {
                //     let wpvrPageTitleAction = document.querySelector('.page-title-action');
                //     let createTourModal = document.querySelector('.wpvr-create-tour-modal');
                //     let tourCloseButton = document.querySelector('.wpvr-create-tour-modal-close');
                //     let TourCancelButton = document.querySelector('.wpvr-modal-btn.modal-cancel');
                //     let createTourModalInner = document.querySelector('.wpvr-create-tour-inner');
                //     let createTourModalWrapper = document.querySelector('.wpvr-create-tour-modal-wrapper');

                    
                //     wpvrPageTitleAction.addEventListener('click', function (e) {
                //         e.preventDefault();

                //         // Show the modal
                //         createTourModal.classList.add('show');
                //     });


                //     // Hide the modal when clicking the close button
                //     tourCloseButton.addEventListener('click', function() {
                //         createTourModal.classList.remove('show');
                //     });

                //     // Hide the modal when clicking the cancel button
                //     TourCancelButton.addEventListener('click', function() {
                //         createTourModal.classList.remove('show');
                //     });

                //     // Hide the modal when clicking outside of the modal-content but inside the modal
                //     window.addEventListener('click', function(event) {
                //         if (event.target === createTourModalInner) {
                //             createTourModal.classList.remove('show');
                //         }
                //     });

                //     // Prevent closing the modal when clicking inside the modal-wrapper
                //     createTourModalWrapper.addEventListener('click', function(event) {
                //         event.stopPropagation();
                //     });


                //     // Get the radio buttons and input field
                //     let radioButtons = document.querySelectorAll('input[name="tour-type"]');
                //     let inputField = document.querySelector('.wpvr-tour-name-input');
                //     let createTour = document.querySelector('.wpvr-modal-btn.modal-submit');
                //     var tourType = 'image-tour';
                //     var tourTitle = '';

                //     // Function to handle radio button change
                //     function handleRadioChange() {
                //         tourType = document.querySelector('input[name="tour-type"]:checked').value;
                //     }

                //     // Function to handle input field change
                //     function handleInputChange() {
                //         tourTitle = inputField.value;
                //     }

                //     // Attach event listeners to radio buttons
                //     radioButtons.forEach(function(radio) {
                //         radio.addEventListener('change', handleRadioChange);
                //     });

                //     // Attach event listener to input field
                //     inputField.addEventListener('input', handleInputChange);

                //     // Attach event listener to create tour button
                //     createTour.addEventListener('click', function (e) {
                //         let targetLink = '<?php //echo get_admin_url() ?>post-new.php?post_type=wpvr_item&tour_type='+tourType+'&tour_title='+tourTitle+'';
                //         window.location.href = targetLink;
                //     });

                // });
            </script>
            <?php

        }
        
        
        /**
         * Sets the value of the title input field with the tour title.
         *
         * This code is executed when the current screen is the post editor for the WPVR post type,
         * the action is 'add', and the tour_title parameter is provided in the URL query string.
         *
         * @param WP_Screen $current_screen The current screen object.
        */
        
        // if(!empty($current_screen->post_type) && $current_screen->post_type == $this->post_type && $current_screen->base == 'post' && $current_screen->action == 'add') {
        //     $tour_title = $_GET['tour_title'];
            
            ?>
            <!-- <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function () {
                    var titleInput = document.querySelector('#titlewrap #title');

                    if (titleInput) {
                        titleInput.value = '<?php //echo $tour_title ?>';
                    }
                });
            </script> -->
            <?php
        //}

    }

    /**
     * Adds a custom body class (.wpvr-tour-list) to the admin page.
     *
     * @return string The custom body class.
     */
    public function add_body_class($classes)
    {
        $current_screen = get_current_screen();
        if (!empty($current_screen->post_type) && $current_screen->post_type == $this->post_type && $current_screen->base == 'edit') {
            $classes .= 'wpvr-tour-list';
        }

        return $classes;
    }

	/**
     * Register the custom post type
     *
     * @since 8.0.0
     */
    public function register()
    {
        $labels = array(
            'name'              => __('Tours', $this->plugin_name),
            'singular_name'     => __('Tours', $this->plugin_name),
            'add_new'           => __('Add New Tour', $this->plugin_name),
            'add_new_item'      => __('Add New Tour', $this->plugin_name),
            'edit_item'         => __('Edit Tour', $this->plugin_name),
            'new_item'          => __('New Tour', $this->plugin_name),
            'view_item'         => __('View Tour', $this->plugin_name),
            'search_items'      => __('Search WP VR Tour', $this->plugin_name),
            'not_found'         => __('No WP VR Tour found', $this->plugin_name),
            'not_found_in_trash'=> __('No WP VR Tour found in Trash', $this->plugin_name),
            'parent_item_colon' => '',
            'all_items'         => __('All Tours', $this->plugin_name),
            'menu_name'         => __('WP VR', $this->plugin_name),
        );

        $args = array(
            'labels'          => $labels,
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'   	=> false,
            'menu_position'   => 100,
            'supports'        => array( 'title' ),
            'menu_icon'           => plugins_url(). '/wpvr/images/icon.png',
            'capabilities' => array(
                    'edit_post' => 'edit_wpvr_tour',
                    'edit_posts' => 'edit_wpvr_tours',
                    'edit_others_posts' => 'edit_other_wpvr_tours',
                    'publish_posts' => 'publish_wpvr_tours',
                    'read_post' => 'read_wpvr_tour',
                    'read_private_posts' => 'read_private_wpvr_tours',
                    'delete_post' => 'delete_wpvr_tour'
            ),
            'map_meta_cap'    => true,
        );

        /**
         * Documentation : https://codex.wordpress.org/Function_Reference/register_post_type
         */
        register_post_type($this->post_type, $args);
    }

    public function copy_shortcode_html()
    {
        $copy = '<span id="wpvr-copy-shortcode-listing" class="wpvr-copy-shortcode-listing">
                    <svg width="19" height="19" fill="none" viewBox="0 0 19 19" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.3 6.95H8.65a1.7 1.7 0 00-1.7 1.7v7.65a1.7 1.7 0 001.7 1.7h7.65a1.7 1.7 0 001.7-1.7V8.65a1.7 1.7 0 00-1.7-1.7z"/><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.55 12.05H2.7a1.7 1.7 0 01-1.7-1.7V2.7A1.7 1.7 0 012.7 1h7.65a1.7 1.7 0 011.7 1.7v.85"/></svg>
                    <span class="copy-shortcode-text">Copied</span>
                </span>';

        return $copy;
    }

    /**
     * @param $columns
     * @return mixed
     *
     * Choose the columns you want in
     * the admin table for this post
     * @since 8.0.0
     */
    public function set_columns($columns) {
        // Set/unset post type table columns 
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'title'         => __('Title', $this->plugin_name),
            'thumbnail'     => __('Thumbnail', $this->plugin_name),
            'shortcode'     => __('Shortcodes', $this->plugin_name),
            'type'          => __('Type', $this->plugin_name),
            'author'        => __('Author', $this->plugin_name),
            'date'          => __('Date', $this->plugin_name)
        );
        return $columns;
    }
 
    /**
     * @param $column
     * @param $post_id
     *
     * Edit the contents of each column in
     * the admin table for this post
     * @since 8.0.0
     */
    public function edit_columns($column) {
        // Post type table column content
        $post = get_post();
        $postdata = get_post_meta($post->ID, 'panodata', true);

        $image_tour_icon = '<svg width="16" height="16" fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M5.9 15h4.2c3.5 0 4.9-1.4 4.9-4.9V5.9C15 2.4 13.6 1 10.1 1H5.9C2.4 1 1 2.4 1 5.9v4.2C1 13.6 2.4 15 5.9 15z"/><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M5.9 6.6a1.4 1.4 0 100-2.8 1.4 1.4 0 000 2.8zm-4.431 6.265l3.45-2.317c.554-.37 1.352-.329 1.849.098l.23.203c.547.47 1.429.47 1.975 0l2.912-2.499c.546-.469 1.428-.469 1.974 0l1.14.98"/></svg>';

        $video_tour_icon = '<svg width="14" height="14" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg"><path fill="#73707D" d="M13.168 5.922a1.344 1.344 0 00-1.468.292l-.255.255a2.937 2.937 0 00-1.277-1.433 2.908 2.908 0 00.914-2.12 2.915 2.915 0 00-5.25-1.748A2.913 2.913 0 003.5 0 2.92 2.92 0 00.583 2.917c0 .834.352 1.587.915 2.12A2.919 2.919 0 000 7.583v3.5A2.92 2.92 0 002.917 14H8.75a2.922 2.922 0 002.695-1.802l.256.255a1.343 1.343 0 001.467.292 1.34 1.34 0 00.831-1.244V7.168c0-.547-.326-1.036-.83-1.245zM8.166 1.167c.965 0 1.75.785 1.75 1.75s-.785 1.75-1.75 1.75c-.964 0-1.75-.785-1.75-1.75s.786-1.75 1.75-1.75zm-4.666 0c.965 0 1.75.785 1.75 1.75s-.785 1.75-1.75 1.75-1.75-.785-1.75-1.75.785-1.75 1.75-1.75zm7 9.917c0 .964-.786 1.75-1.75 1.75H2.917c-.965 0-1.75-.786-1.75-1.75v-3.5c0-.965.785-1.75 1.75-1.75H8.75c.964 0 1.75.785 1.75 1.75v3.5zm2.333.416c0 .1-.06.146-.112.167a.17.17 0 01-.196-.04l-.859-.858V7.898l.859-.859A.17.17 0 0112.72 7a.17.17 0 01.112.167V11.5z"/></svg>';

        $streetview_icon = '<svg width="16" height="17" fill="none" viewBox="0 0 16 17" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-miterlimit="10" stroke-width="1.2" d="M6.74 16l1.015-1.278-1.015-1.278"/><path stroke="#73707D" stroke-miterlimit="10" stroke-width="1.2" d="M6.37 10.507C3.305 10.71 1 11.566 1 12.592c0 1.177 3.024 2.13 6.754 2.13m2.188-.114c2.656-.287 4.566-1.08 4.566-2.016 0-1.01-2.227-1.855-5.217-2.074m-.184-8.165a1.352 1.352 0 10-2.705 0 1.352 1.352 0 002.705 0z"/><path stroke="#73707D" stroke-miterlimit="10" stroke-width="1.2" d="M10.275 6.226v.952c0 .578-.468 1.045-1.045 1.045v3.566H6.28V8.223a1.045 1.045 0 01-1.046-1.045v-.952a2.52 2.52 0 115.041 0z"/></svg>';

        

        switch ($column) {
            case 'shortcode':
                echo '<div class="wpvr-listing-shortcode"><span class="wpvr-code">[wpvr id="' . $post->ID . '"] </span>' . $this->copy_shortcode_html() . '</div>';
                break;

            case 'thumbnail':
                $scene_image = !empty($postdata['preview']) ?  $postdata['preview'] : $this->get_scene_image($postdata);
                
                if ($scene_image) {
                    echo '<img loading="lazy" src="' . esc_url($scene_image) . '" class="wpvr-thumbnail-image" alt="thumbnail" width="73" height="54">';
                }else {
                    echo '<svg width="74" height="54" fill="none" viewBox="0 0 74 54" xmlns="http://www.w3.org/2000/svg"><rect width="73.517" height="54" fill="#E3E3E3" rx="5"/><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M34.9 34h4.2c3.5 0 4.9-1.4 4.9-4.9v-4.2c0-3.5-1.4-4.9-4.9-4.9h-4.2c-3.5 0-4.9 1.4-4.9 4.9v4.2c0 3.5 1.4 4.9 4.9 4.9z"/><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M34.9 25.6a1.4 1.4 0 100-2.8 1.4 1.4 0 000 2.8zm-4.431 6.265l3.45-2.317c.554-.37 1.352-.329 1.849.098l.23.203c.547.47 1.429.47 1.975 0l2.912-2.499c.546-.469 1.428-.469 1.974 0l1.14.98"/></svg>';
                }
                
                break;

            case 'type':
                if (isset($postdata['streetviewdata'])) {
                    echo '<span class="tour-type">'. $streetview_icon .'Street View</span>';

                } elseif (isset($postdata['vidid'])) {
                    echo '<span class="tour-type">'. $video_tour_icon .'360 Video</span>';

                } else {
                    echo '<span class="tour-type">'. $image_tour_icon .' 360 Image</span>';
                }
                break;

            default:
                break;
        }
    }

    /**
     * Get the scene image for the post
     *
     * @param $postdata array The post meta data
     * @return string
     * @since 8.5.22
     */
    private function get_scene_image($postdata) {
        if (!empty($postdata['panodata']['scene-list'][1]['scene-type']) && $postdata['panodata']['scene-list'][1]['scene-type'] === 'equirectangular') {
            $image_src = $postdata['panodata']['scene-list'][1]['scene-attachment-url'] ?? '';
            $thumbnail = $this->wpvr_fetch_thumbnail($image_src);
            if ($thumbnail) {
                return $thumbnail;
            }
        }

        $image_src_face = $postdata['panodata']['scene-list'][1]['scene-attachment-url-face0'] ?? '';
        return $this->wpvr_fetch_thumbnail($image_src_face);
    }

    /**
     * Fetches the thumbnail for the given image URL
     *
     * @param string $image_url The URL of the image
     * @return string The URL of the thumbnail
     * @since 8.5.22
     */
    private function wpvr_fetch_thumbnail($image_url) {
        if (empty($image_url)) {
            return '';
        }

        $attachment_id = attachment_url_to_postid($image_url);
        if ($attachment_id) {
            $thumb_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
            if ($thumb_url) {
                return $thumb_url;
            }
        }

        $attachment_id = $this->wpvr_get_attachment_id_by_url($image_url);
        if ($attachment_id) {
            $thumb_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
            if ($thumb_url) {
                return $thumb_url;
            }
        }

        return $this->wpvr_generate_thumbnail($image_url);
    }

    /**
     * Get the attachment ID by URL
     *
     * @param string $image_url The URL of the image
     * @return int The ID of the attachment
     * @since 8.5.22
     */
    private function wpvr_get_attachment_id_by_url($image_url) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE guid=%s AND post_type='attachment'",
            $image_url
        ));
    }

    /**
     * Generates a thumbnail for the given image URL
     *
     * @param string $image_url The URL of the image
     * @return string The URL of the thumbnail
     * @since 8.5.22
     */
    private function wpvr_generate_thumbnail($image_url) {
        $uploads_dir = wp_upload_dir();
        $image_path = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $image_url);

        if (file_exists($image_path)) {
            $image = wp_get_image_editor($image_path);
            if (!is_wp_error($image)) {
                $thumb_path = dirname($image_path) . '/thumb-' . basename($image_path);
                $image->resize(150, 150, true);
                $image->save($thumb_path);
                return str_replace($uploads_dir['basedir'], $uploads_dir['baseurl'], $thumb_path);
            }
        }

        return $image_url;
    }


    /**
     * Sets the messages for the custom post type
     *
     * @since 8.0.0
     */
    public function wpvr_post_updated_messages($messages)
    {
        $messages[$this->post_type][1] = __('WP VR item updated.', $this->plugin_name);
        $messages[$this->post_type][4] = __('WP VR item updated.', $this->plugin_name);

        return $messages;
    }

}
