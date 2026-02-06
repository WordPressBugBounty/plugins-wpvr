<?php

/**
 * WPVR First Tour Banner Class
 *
 * This class handles the display of an encouragement banner for users
 * who haven't published their first tour yet.
 *
 * @since 8.5.44
 */
class WPVR_First_Tour_Banner {

    /**
     * Option name for storing dismissal timestamp
     *
     * @var string
     */
    private $dismissal_option = 'wpvr_tour_banner_dismissed';

    /**
     * Banner ID for tracking
     *
     * @var string
     */
    private $banner_id = 'wpvr-tour-banner';

    /**
     * Constructor
     */
    public function __construct() {
        // Only initialize if banner should be shown
        if ($this->should_show_banner()) {
            add_action('admin_notices', array($this, 'display_banner'));
            add_action('admin_head', array($this, 'add_styles'));
            add_action('wp_ajax_wpvr_dismiss_tour_banner', array($this, 'handle_dismiss_banner'));
        }
    }

    /**
     * Check if banner should be shown
     *
     * @return bool
     */
    private function should_show_banner() {
        // Check if banner was dismissed (7 days ago or less)
        $dismissed_time = get_option($this->dismissal_option, 0);
        if ($dismissed_time && (time() - $dismissed_time) < (7 * DAY_IN_SECONDS)) {
            return false;
        }

        // Check if user has published tours (excluding demo tours)
        $published_tours = $this->get_published_tours_count();

        // Show banner if no published tours exist, or if tours exist but Pro is not active
        if ($published_tours === 0) {
            return true;
        } elseif ($published_tours > 0 && !defined('WPVR_PRO_VERSION')) {
            return true;
        }
        
        return false;
    }

    /**
     * Get count of published tours (excluding demo tours)
     *
     * @return int
     */
    private function get_published_tours_count() {
        $args = array(
            'post_type'      => 'wpvr_item',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => 'wpvr_is_demo_tour',
                    'compare' => 'NOT EXISTS', // Exclude demo tours
                ),
            ),
        );

        $tours = get_posts($args);
        return count($tours);
    }

    /**
     * Display the banner
     */
    public function display_banner() {
        // Only render if we're on a WPVR admin page
        if (!$this->is_wpvr_admin_page()) {
            return;
        }
        $this->render_banner_html();
    }

    /**
     * Render banner HTML
     */
    private function render_banner_html() {
        $create_tour_url = admin_url('post-new.php?post_type=wpvr_item');
        $upgrade_url = 'https://rextheme.com/wpvr/wpvr-pricing/';
        $published_tours = $this->get_published_tours_count();
        
        // Determine which message to show
        if ($published_tours === 0) {
            // No tours published yet
            $message = sprintf(
                wp_kses(
                    __('You haven\'t published your first tour yet. <a href="%s">Publish your first tour</a> and see it live in minutes!', 'wpvr'),
                    array(
                        'a' => array(
                            'href' => array(),
                            'class' => array()
                        )
                    )
                ),
                esc_url($create_tour_url)
            );
        } else {
            // Tours published but Pro not active
            $message = sprintf(
                wp_kses(
                    __('You\'ve published your first tour! <a href="%s" target="_blank" rel="noopener">Upgrade to Pro</a> for unlimited tours, hotspots, and advanced features!', 'wpvr'),
                    array(
                        'a' => array(
                            'href' => array(),
                            'target' => array(),
                            'rel' => array(),
                            'class' => array()
                        )
                    )
                ),
                esc_url($upgrade_url)
            );
        }
        ?>
        <div id="<?php echo esc_attr($this->banner_id); ?>" class="wpvr-tour-banner notice notice-info">
            <div class="wpvr-tour-banner__content">
                <div class="wpvr-tour-banner__text">
                    <p><?php echo wp_kses_post($message); ?></p>
                </div>
            </div>
            <button type="button" class="wpvr-tour-banner__close" aria-label="Dismiss this notice">
                <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23 11.5C23 17.8513 17.8513 23 11.5 23C5.14873 23 0 17.8513 0 11.5C0 5.14873 5.14873 0 11.5 0C17.8513 0 23 5.14873 23 11.5Z" fill="#FFECE2"/>
                    <path d="M16 8.63687L14.3631 7L11.5 9.86313L8.63687 7L7 8.63687L9.86313 11.5L7 14.3631L8.63687 16L11.5 13.1369L14.3631 16L16 14.3631L13.1369 11.5L16 8.63687Z" fill="#E56829"/>
                </svg>
            </button>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '#<?php echo esc_attr($this->banner_id); ?> .wpvr-tour-banner__close', function() {
                wpvr_dismiss_tour_banner();
            });

            function wpvr_dismiss_tour_banner() {
                // Hide the banner immediately with fade effect
                var $banner = $('#<?php echo esc_attr($this->banner_id); ?>');
                
                $banner.fadeOut(300, function() {
                    $(this).remove();
                });
                
                // Send AJAX request to store dismissal in database
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpvr_dismiss_tour_banner',
                        nonce: '<?php echo wp_create_nonce('wpvr_dismiss_banner'); ?>'
                    },
                    success: function(response) {
                        console.log('Banner dismissed successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error dismissing banner:', error);
                        // Even if AJAX fails, banner is already hidden from user perspective
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Add banner styles
     */
    public function add_styles() {
        ?>
        <style id="wpvr-tour-banner-styles" type="text/css">
            .wpvr-tour-banner {
                position: relative;
                padding: 16px 46px 16px 20px;
                border: 1px solid #3F04FE;
                border-radius: 4px;
                background: #fff;
                margin: 20px 20px 0 0;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            }

            .wpvr-tour-banner__content {
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                position: relative;
            }

            .wpvr-tour-banner__text {
                flex: 1;
                text-align: center;
            }

            .wpvr-tour-banner__text p {
                margin: 0;
                font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-weight: 600;
                font-style: normal;
                font-size: 16px;
                leading-trim: none;
                line-height: 100%;
                letter-spacing: 0%;
                text-align: center;
                color: #3c434a;
            }

            .wpvr-tour-banner__text a {
                color: #3F04FE;
                text-decoration: none;
                font-weight: 500;
                font-style: normal;
                border-bottom: 1px solid #3F04FE;
            }

            /* Position dismiss button in the middle right */
            .wpvr-tour-banner__close {
                position: absolute;
                top: 50%;
                right: 12px;
                transform: translateY(-50%);
                background: none;
                border: none;
                padding: 0;
                cursor: pointer;
                width: 23px;
                height: 23px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: opacity 0.2s ease;
            }

            .wpvr-tour-banner__close:hover {
                opacity: 0.8;
            }

            .wpvr-tour-banner__close:active {
                transform: translateY(-50%) scale(0.95);
            }

            .wpvr-tour-banner__close svg {
                display: block;
                width: 23px;
                height: 23px;
            }

            /* Hide banner if dismissed via JavaScript */
            .wpvr-tour-banner.dismissed {
                display: none;
            }

            /* Responsive styles */
            @media screen and (max-width: 782px) {
                .wpvr-tour-banner {
                    padding: 16px 46px 16px 16px;
                    margin: 16px 16px 0 0;
                }

                .wpvr-tour-banner__content {
                    flex-direction: column;
                    text-align: center;
                    justify-content: center;
                }

                .wpvr-tour-banner__close {
                    right: 8px;
                }
            }
        </style>
        <?php
    }

    /**
     * Check if current screen is a WPVR admin page
     *
     * @return bool
     */
    private function is_wpvr_admin_page() {
        // Check if we're in admin and the function is available
        if (!is_admin() || !function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }

        $wpvr_pages = array(
            'toplevel_page_wpvr',
            'wp-vr_page_wpvr-setting',
            'edit-wpvr_item',
            'wpvr_item',
            'wp-vr_page_wpvr-setup-wizard',
            'wp-vr_page_wpvr-analytics',
            'wp-vr_page_wpvrpro',
            'admin_page_rex-wpvr-setup-wizard',
            'dashboard_page_rex-wpvr-setup-wizard',
        );

        return in_array($screen->id, $wpvr_pages, true);
    }

    /**
     * Handle AJAX request to dismiss banner
     */
    public function handle_dismiss_banner() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpvr_dismiss_banner')) {
            wp_die('Invalid nonce');
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // Store dismissal timestamp
        update_option($this->dismissal_option, time());

        wp_send_json_success(array('message' => 'Banner dismissed successfully'));
    }
}