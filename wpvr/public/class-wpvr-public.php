<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      8.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/public
 */

use WPVR\Builder\DIVI\WPVR_Divi_modules;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpvr
 * @subpackage Wpvr/public
 * @author     Rextheme <support@rextheme.com>
 */
class Wpvr_Public {

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
     * Instance of WPVR_Shortcode class
     * 
     * @var object
     * @since 8.0.0
     */
    private $shortcode;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    8.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->shortcode = new WPVR_Shortcode($this->plugin_name);

	}

	/**
	 * Check if the current page contains a WP VR tour
	 *
	 * @since    8.5.67
	 * @return   bool    True if tour is present, false otherwise
	 */
	protected function has_wpvr_tour_on_page() {
		global $post;
		
		// Use queried object as fallback when $post is not set
		if (!$post) {
			$post = get_queried_object();
		}
		
		if (!($post instanceof WP_Post)) {
			return false;
		}
		
		// Check if current post is a wpvr_item
		if ('wpvr_item' === $post->post_type) {
			return true;
		}
		
		// Check for shortcode in post content
		if (has_shortcode($post->post_content, 'wpvr')) {
			return true;
		}
		
		// Check for Gutenberg block
		if (function_exists('has_block') && has_block('wpvr/wpvr-block', $post)) {
			return true;
		}
		
		// Check for page builder content
		// Elementor stores content in _elementor_data meta
		$elementor_data = get_post_meta($post->ID, '_elementor_data', true);
		if (!empty($elementor_data) && strpos($elementor_data, 'wpvr') !== false) {
			return true;
		}
		
		// Bricks stores content in _bricks_page_content_2 meta
		$bricks_data = get_post_meta($post->ID, '_bricks_page_content_2', true);
		if (!empty($bricks_data) && is_array($bricks_data)) {
			$bricks_json = json_encode($bricks_data);
			if (strpos($bricks_json, 'wpvr') !== false) {
				return true;
			}
		}
		
		// Oxygen stores content in ct_builder_shortcodes meta
		$oxygen_data = get_post_meta($post->ID, 'ct_builder_shortcodes', true);
		if (!empty($oxygen_data) && strpos($oxygen_data, 'wpvr') !== false) {
			return true;
		}
		
		// Divi stores content in post_content with shortcodes
		// Already covered by has_shortcode check above
		
		// WPBakery stores content in post_content with shortcodes
		// Already covered by has_shortcode check above
		
		return false;
	}

	/**
	 * Check if the current page contains both video and panorama tours
	 *
	 * @since    8.5.68
	 * @return   bool    True if page contains both tour types, false otherwise
	 */
	protected function has_mixed_tours_on_page() {
		$has_video = $this->has_video_tour_on_page();
		$has_panorama = $this->has_panorama_tour_on_page();
		return $has_video && $has_panorama;
	}

	/**
	 * Check if the current page contains a panorama (360 image) tour
	 *
	 * @since    8.5.68
	 * @return   bool    True if page contains panorama tour, false otherwise
	 */
	protected function has_panorama_tour_on_page() {
		global $post;
		
		// Use queried object as fallback when $post is not set
		if (!$post) {
			$post = get_queried_object();
		}
		
		if (!($post instanceof WP_Post)) {
			return false;
		}
		
		// Check if current post is a wpvr_item without video
		if ('wpvr_item' === $post->post_type) {
			$panodata = get_post_meta($post->ID, 'panodata', true);
			if (empty($panodata) || !isset($panodata['vidid']) || empty($panodata['vidid'])) {
				return true;
			}
		}
		
		// Check for shortcode with panorama tour ID
		if (has_shortcode($post->post_content, 'wpvr')) {
			// Extract tour IDs from shortcodes
			preg_match_all('/\[wpvr[^\]]*id=["\']?(\d+)["\']?[^\]]*\]/i', $post->post_content, $matches);
			if (isset($matches[1]) && !empty($matches[1])) {
				foreach ($matches[1] as $tour_id) {
					$panodata = get_post_meta($tour_id, 'panodata', true);
					if (empty($panodata) || !isset($panodata['vidid']) || empty($panodata['vidid'])) {
						return true;
					}
				}
			}
		}
		
		// For Gutenberg blocks, parse block content to extract tour IDs
		if (function_exists('has_block') && has_block('wpvr/wpvr-block', $post)) {
			$blocks = parse_blocks($post->post_content);
			foreach ($blocks as $block) {
				if ($block['blockName'] === 'wpvr/wpvr-block' && isset($block['attrs']['id'])) {
					$tour_id = $block['attrs']['id'];
					$panodata = get_post_meta($tour_id, 'panodata', true);
					if (empty($panodata) || !isset($panodata['vidid']) || empty($panodata['vidid'])) {
						return true;
					}
				}
			}
		}
		
		// For page builders (Elementor, Bricks, Oxygen), we can't easily extract tour IDs
		// So we return false and let the general tour detection handle it
		
		return false;
	}

	/**
	 * Check if the current page contains a video tour
	 * Video tours require video.js and videojs-vr libraries
	 *
	 * @since    8.5.68
	 * @return   bool    True if page contains video tour, false otherwise
	 */
	protected function has_video_tour_on_page() {
		global $post;
		
		// Use queried object as fallback when $post is not set
		if (!$post) {
			$post = get_queried_object();
		}
		
		if (!($post instanceof WP_Post)) {
			return false;
		}
		
		// Check if current post is a wpvr_item with video
		if ('wpvr_item' === $post->post_type) {
			$panodata = get_post_meta($post->ID, 'panodata', true);
			if (isset($panodata['vidid']) && !empty($panodata['vidid'])) {
				return true;
			}
		}
		
		// Check for shortcode with video tour ID
		if (has_shortcode($post->post_content, 'wpvr')) {
			// Extract tour IDs from shortcodes
			preg_match_all('/\[wpvr[^\]]*id=["\']?(\d+)["\']?[^\]]*\]/i', $post->post_content, $matches);
			if (isset($matches[1]) && !empty($matches[1])) {
				foreach ($matches[1] as $tour_id) {
					$panodata = get_post_meta($tour_id, 'panodata', true);
					if (isset($panodata['vidid']) && !empty($panodata['vidid'])) {
						return true;
					}
				}
			}
		}
		
		// For Gutenberg blocks and page builders, we need to check all wpvr_item posts
		// to see if any contain video tours. This is a conservative approach.
		// If we can't determine definitively, we'll check if ANY tour on the page is a video tour
		if (function_exists('has_block') && has_block('wpvr/wpvr-block', $post)) {
			// Parse block content to extract tour IDs
			$blocks = parse_blocks($post->post_content);
			foreach ($blocks as $block) {
				if ($block['blockName'] === 'wpvr/wpvr-block' && isset($block['attrs']['id'])) {
					$tour_id = $block['attrs']['id'];
					$panodata = get_post_meta($tour_id, 'panodata', true);
					if (isset($panodata['vidid']) && !empty($panodata['vidid'])) {
						return true;
					}
				}
			}
		}
		
		// For page builders (Elementor, Bricks, Oxygen), we can't easily extract tour IDs
		// So we'll return false and let Video Script Control handle video-specific pages
		// This is a conservative approach to avoid loading video libraries unnecessarily
		
		return false;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpvr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpvr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		global $wp;
        $wpvr_script_control = get_option('wpvr_script_control');
        $wpvr_script_list = get_option('wpvr_script_list');
        $allowed_pages_modified = array();
        $allowed_pages = isset($wpvr_script_list) && !empty($wpvr_script_list) ? array_map('sanitize_text_field', explode(",", $wpvr_script_list)) : array();
        foreach ($allowed_pages as $value) {
            $allowed_pages_modified[] = untrailingslashit($value);
        }
        $current_url = home_url(add_query_arg(isset($_GET) ? array_map('sanitize_text_field', wp_unslash($_GET)) : array(), isset($wp->request) ? sanitize_text_field($wp->request) : ''));
        if ($wpvr_script_control == 'true') {
            foreach ($allowed_pages_modified as $value) {
                if ($value) {
                    if (strpos($current_url, $value) !== false) {
                        $fontawesome_disable = get_option('wpvr_fontawesome_disable');
                        if ($fontawesome_disable == 'true') {
                        } else {
                            wp_enqueue_style($this->plugin_name . 'fontawesome', plugin_dir_url(__FILE__) . 'css/fontawesome/css/all.css', array(), $this->version, 'all');
                            wp_enqueue_style(
                                $this->plugin_name . '-icons-fix',
                                plugin_dir_url(__FILE__) . 'css/fontawesome/css/icons-fix.css',
                                array($this->plugin_name . 'fontawesome'),
                                $this->version
                            );
                        }
                        
                        // Check if page contains mixed tours (both video and panorama)
                        $has_mixed = $this->has_mixed_tours_on_page();
                        $is_video_tour = $this->has_video_tour_on_page();
                        $is_panorama_tour = $this->has_panorama_tour_on_page();
                        
                        if ($has_mixed) {
                            // Load all resources for mixed tours
                            wp_enqueue_style('videojs-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/video-js.css', array(), true);
                            wp_enqueue_style('videojs-vr-css', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.css', array(), true);
                            wp_enqueue_style('panellium-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/pannellum.css', array(), true);
                            wp_enqueue_style('owl-css', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
                        } elseif ($is_video_tour) {
                            // For video tours: only load video CSS
                            wp_enqueue_style('videojs-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/video-js.css', array(), true);
                            wp_enqueue_style('videojs-vr-css', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.css', array(), true);
                        } elseif ($is_panorama_tour) {
                            // For panorama tours: load panorama CSS
                            wp_enqueue_style('panellium-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/pannellum.css', array(), true);
                            wp_enqueue_style('owl-css', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
                        }
                        
                        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-public.css', array(), $this->version, 'all');
                    }
                }
            }
        } else {
            // Only load assets if tour is present on the page
            if ($this->has_wpvr_tour_on_page()) {
                $fontawesome_disable = get_option('wpvr_fontawesome_disable');
                if ($fontawesome_disable == 'true') {
                } else {
                    wp_enqueue_style($this->plugin_name . 'fontawesome', plugin_dir_url(__FILE__) . 'css/fontawesome/css/all.css', array(), $this->version, 'all');
                    wp_enqueue_style(
                        $this->plugin_name . '-icons-fix',
                        plugin_dir_url(__FILE__) . 'css/fontawesome/css/icons-fix.css',
                        array($this->plugin_name . 'fontawesome'),
                        $this->version
                    );
                }
                
                // Check if page contains mixed tours (both video and panorama)
                $has_mixed = $this->has_mixed_tours_on_page();
                $is_video_tour = $this->has_video_tour_on_page();
                $is_panorama_tour = $this->has_panorama_tour_on_page();
                
                if ($has_mixed) {
                    // Load all resources for mixed tours
                    wp_enqueue_style('videojs-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/video-js.css', array(), true);
                    wp_enqueue_style('videojs-vr-css', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.css', array(), true);
                    wp_enqueue_style('panellium-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/pannellum.css', array(), true);
                    wp_enqueue_style('owl-css', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
                } elseif ($is_video_tour) {
                    // For video tours: only load video CSS
                    wp_enqueue_style('videojs-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/video-js.css', array(), true);
                    wp_enqueue_style('videojs-vr-css', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.css', array(), true);
                } elseif ($is_panorama_tour) {
                    // For panorama tours: load panorama CSS
                    wp_enqueue_style('panellium-css', plugin_dir_url(__FILE__) . 'lib/pannellum/src/css/pannellum.css', array(), true);
                    wp_enqueue_style('owl-css', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
                }
                
                wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpvr-public.css', array(), $this->version, 'all');
            }
        }

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_scripts() {

		$notice = '';
        $wpvr_frontend_notice = get_option('wpvr_frontend_notice');
        if ($wpvr_frontend_notice) {
            $notice = sanitize_text_field( get_option('wpvr_frontend_notice_area') );
        }
        global $wp;
        $wpvr_script_control = get_option('wpvr_script_control');
        $wpvr_script_list = get_option('wpvr_script_list');
        $allowed_pages_modified = array();
        $allowed_pages = isset($wpvr_script_list) && !empty($wpvr_script_list) ? array_map('sanitize_text_field', explode(",", $wpvr_script_list)) : array();
        foreach ($allowed_pages as $value) {
            $allowed_pages_modified[] = untrailingslashit($value);
        }

        $wpvr_video_script_control = get_option('wpvr_video_script_control');
        $wpvr_video_script_list = get_option('wpvr_video_script_list');
        $allowed_video_pages_modified = array();
        $allowed_video_pages = isset($wpvr_video_script_list) && !empty($wpvr_video_script_list) ? array_map('sanitize_text_field', explode(",", $wpvr_video_script_list)) : array();
        foreach ($allowed_video_pages as $value) {
            $allowed_video_pages_modified[] = untrailingslashit($value);
        }

        $current_url = home_url(add_query_arg(isset($_GET) ? array_map('sanitize_text_field', wp_unslash($_GET)) : array(), isset($wp->request) ? sanitize_text_field($wp->request) : ''));

        if ($wpvr_script_control == 'true') {
            foreach ($allowed_pages_modified as $value) {
                if (strpos($current_url, $value) !== false) {
                    // Check if page contains mixed tours (both video and panorama)
                    $has_mixed = $this->has_mixed_tours_on_page();
                    $is_video_tour = $this->has_video_tour_on_page();
                    $is_panorama_tour = $this->has_panorama_tour_on_page();
                    
                    if ($has_mixed) {
                        // Load all resources for mixed tours
                        wp_enqueue_script('videojs-js', plugin_dir_url(__FILE__) . 'js/video.js', array(), true);
                        wp_enqueue_script('videojsvr-js', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.js', array('videojs-js'), true);
                        wp_enqueue_script('panellium-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js', array(), true);
                        wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), true);
                        wp_enqueue_script('panelliumvid-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/videojs-pannellum-plugin.js', array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js'), true);
                        wp_enqueue_script('owl-js', plugin_dir_url(__FILE__) . 'js/owl.carousel.js', array('jquery'), false);
                    } elseif ($is_video_tour) {
                        // For video tours: only load video JS
                        wp_enqueue_script('videojs-js', plugin_dir_url(__FILE__) . 'js/video.js', array(), true);
                        wp_enqueue_script('videojsvr-js', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.js', array('videojs-js'), true);
                        wp_enqueue_script('panellium-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js', array(), true);
                        wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), true);
                        wp_enqueue_script('panelliumvid-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/videojs-pannellum-plugin.js', array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js'), true);
                    } elseif ($is_panorama_tour) {
                        // For panorama tours: load panorama JS
                        wp_enqueue_script('panellium-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js', array(), true);
                        wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), true);
                        wp_enqueue_script('owl-js', plugin_dir_url(__FILE__) . 'js/owl.carousel.js', array('jquery'), false);
                    }
                    
                    wp_enqueue_script('jquery_cookie', plugin_dir_url(__FILE__) . 'js/jquery.cookie.js', array('jquery'), true);
                    wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpvr-public.js', array('jquery', 'jquery_cookie'), $this->version, false);
                    wp_localize_script('wpvr', 'wpvr_public', array(
                        'notice_active' => $wpvr_frontend_notice,
                        'notice' => $notice,
                        'is_pro_active' => is_plugin_active('wpvr-pro/wpvr-pro.php'),
                        'is_license_active' => get_option('wpvr_edd_license_status') == 'valid' ? true : false,
                        'dis_on_hover' => get_option('dis_on_hover') === 'true' ? true : false,
                        'mobile_hotspot_tip' => get_option('wpvr_mobile_hotspot_tip') === 'true' ? true : false,
                    ));
                }
            }
        } else {
            // Only load scripts if tour is present on the page
            if ($this->has_wpvr_tour_on_page()) {
                // Check if page contains mixed tours (both video and panorama)
                $has_mixed = $this->has_mixed_tours_on_page();
                $is_video_tour = $this->has_video_tour_on_page();
                $is_panorama_tour = $this->has_panorama_tour_on_page();
                
                if ($has_mixed) {
                    // Load all resources for mixed tours
                    wp_enqueue_script('videojs-js', plugin_dir_url(__FILE__) . 'js/video.js', array(), true);
                    wp_enqueue_script('videojsvr-js', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.js', array('videojs-js'), true);
                    wp_enqueue_script('panellium-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js', array(), true);
                    wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), true);
                    wp_enqueue_script('panelliumvid-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/videojs-pannellum-plugin.js', array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js'), true);
                    wp_enqueue_script('owl-js', plugin_dir_url(__FILE__) . 'js/owl.carousel.js', array('jquery'), false);
                } elseif ($is_video_tour) {
                    // For video tours: only load video JS
                    wp_enqueue_script('videojs-js', plugin_dir_url(__FILE__) . 'js/video.js', array(), true);
                    wp_enqueue_script('videojsvr-js', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.js', array('videojs-js'), true);
                    wp_enqueue_script('panellium-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js', array(), true);
                    wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), true);
                    wp_enqueue_script('panelliumvid-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/videojs-pannellum-plugin.js', array('videojs-js', 'videojsvr-js', 'panellium-js', 'panelliumlib-js'), true);
                } elseif ($is_panorama_tour) {
                    // For panorama tours: load panorama JS
                    wp_enqueue_script('panellium-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/pannellum.js', array(), true);
                    wp_enqueue_script('panelliumlib-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/libpannellum.js', array('panellium-js'), true);
                    wp_enqueue_script('owl-js', plugin_dir_url(__FILE__) . 'js/owl.carousel.js', array('jquery'), false);
                }
                
                wp_enqueue_script('jquery_cookie', plugin_dir_url(__FILE__) . 'js/jquery.cookie.js', array('jquery'), true);
                wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpvr-public.js', array('jquery', 'jquery_cookie'), $this->version, true);
                wp_localize_script('wpvr', 'wpvr_public', array(
                    'notice_active' => $wpvr_frontend_notice,
                    'notice' => $notice,
                    'is_pro_active' => is_plugin_active('wpvr-pro/wpvr-pro.php'),
                    'is_license_active' => get_option('wpvr_edd_license_status') == 'valid' ? true : false,
                    'dis_on_hover' => get_option('dis_on_hover') === 'true' ? true : false,
                    'mobile_hotspot_tip' => get_option('wpvr_mobile_hotspot_tip') === 'true' ? true : false,
                ));
            }
        }

        $match_found = false;
        if ($wpvr_video_script_control == 'true') {
            foreach ($allowed_video_pages_modified as $value) {
                if (strpos($current_url, $value) !== false) {
                    // Only enqueue video scripts if the page actually contains a video tour
                    if ($this->has_video_tour_on_page()) {
                        $match_found = true;
                        wp_enqueue_script('videojs-js', plugin_dir_url(__FILE__) . 'js/video.js', array(), true); // commented for video js vr
                        wp_enqueue_script('videojsvr-js', plugin_dir_url(__FILE__) . 'lib/videojs-vr/videojs-vr.js', array(), true); //video js vr
                        wp_enqueue_script('panelliumvid-js', plugin_dir_url(__FILE__) . 'lib/pannellum/src/js/videojs-pannellum-plugin.js', array(), true);
                    }
                }
            }
            if (!$match_found) {
                wp_dequeue_script('videojs-js');
                wp_dequeue_script('videojsvr-js');
                wp_dequeue_script('panelliumvid-js');
            }
        }

	}

	/**
     * Init the edit screen of the plugin post type item
     *
     * @since 8.0.0
     */
    public function public_init()
    {
        add_shortcode($this->plugin_name, array( $this->shortcode , 'wpvr_shortcode'));

    }

}
