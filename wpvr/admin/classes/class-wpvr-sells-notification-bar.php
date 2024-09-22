<?php

Class WPVR_Notification_Bar {

  public function __construct() {
    // Hook into the admin_notices action to display the banner
    add_action('admin_notices', array($this, 'display_banner'));
    // Add styles
    add_action('admin_head', array($this, 'add_styles'));

      add_action('wp_ajax_wpvr_sale_notification_notice',  array($this, 'wpvr_sale_notification_notice'));

  }


    /**
     * Displays the special occasion banner if the current date and time are within the specified range.
     */
    public function display_banner()
    {

      $screen                     = get_current_screen();
      $promotional_notice_pages   = ['dashboard', 'plugins', 'edit-wpvr_item', 'toplevel_page_wpvr', 'wp-vr_page_wpvr-setup-wizard', 'wpvr_item', 'wp-vr_page_wpvr-addons','wp-vr_page_wpvr-setting'];
      if (!in_array($screen->id, $promotional_notice_pages)) {
          return;
      }

          $btn_link = 'https://rextheme.com/wpvr/wpvr-pricing/';
      ?>

        <section class="wpvr-promo-banner wpvr-promo-banner--regular" aria-labelledby="wpvr-promo-banner-title" id="wpvr-promo-banner">
            <div class="wpvr-promo-banner__container">
                <h2 class="wpvr-promo-banner__title" id="wpvr-promo-banner-title">
                    <a href="<?php echo esc_url($btn_link); ?>"  target ="_blank" class="wpvr-promo-banner__link" aria-label="Get Special Discount">
                        <?php echo esc_html__('Get ', 'wpvr'); ?><strong class="wpvr-promo-banner__discount"><?php echo esc_html__(' 20% ', 'wpvr'); ?></strong><?php echo esc_html__(' Discount On ', 'wpvr'); ?><strong class="wpvr-promo-banner__discount"><?php echo esc_html__('WPVR', 'wpvr'); ?></strong>
                        <span class="wpvr-promo-banner__icon" aria-hidden="true">
                            <svg class="wpvr-promo-banner__svg" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <title id="arrow-icon-title">Arrow Icon</title>
                                <path d="M8.77887 2.05511L9.03494 1.79904H8.6728H5.17466C4.71928 1.79904 4.35014 1.42989 4.35014 0.97452C4.35014 0.519144 4.71928 0.15 5.17466 0.15H11.0253C11.4807 0.15 11.8498 0.519153 11.8498 0.97452V6.82164C11.8498 7.27701 11.4807 7.64616 11.0253 7.64616C10.5699 7.64616 10.2008 7.27701 10.2008 6.82164V3.32737V2.96524L9.94471 3.22131L1.5575 11.6084L1.55748 11.6085C1.23552 11.9305 0.713541 11.9305 0.391497 11.6085L0.391491 11.6084C0.0695031 11.2865 0.0695031 10.7644 0.391491 10.4424L0.285427 10.3363L0.391493 10.4424L8.77887 2.05511Z" fill="#3F04FE" stroke="white" stroke-width="0.3"/>
                            </svg>
                        </span>
                    </a>
                </h2>

                <a class="wpvr-promo-banner__cross-icon" type="button" aria-label="close banner" id="wpvr-promo-banner__cross-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M11 1L1 11" stroke="#C8C0E2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1 1L11 11" stroke="#C8C0E2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

            </div>

        </section>
        <script>
            (function ($) {
                /**
                 * Dismiss sale notification notice
                 *
                 * @param e
                 */
                function wpvr_sale_notification_notice(e) {
                    e.preventDefault();
                    $('#wpvr-promo-banner').hide(); // Ensure the correct element is selected
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: "wpvr_sale_notification_notice",
                            nonce: wpvr_global_obj.ajax_nonce
                        },
                        success: function(response) {
                            $('#wpvr-promo-banner').hide(); // Ensure the correct element is selected
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX request failed:', status, error);
                        }
                    });
                }

                jQuery(document).ready(function($) {
                    $(document).on('click', '#wpvr-promo-banner__cross-icon', wpvr_sale_notification_notice);
                });
            })(jQuery);
        </script>
      <!-- .rex-feed-tb-notification end -->
      <?php
    }


    /**
     * Adds internal CSS styles for the special occasion banners.
     */
    public function add_styles()
    {
        ?>
         <style id="wpvr-promotional-banner-style" type="text/css">
            :root {
              --vr-primary-color: #3F04FE;
            }

            @font-face {
                font-family: 'Roboto';
                src: url(<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/fonts/Roboto-Regular.woff2'); ?>) format('woff2');
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Roboto';
                src: url(<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/fonts/Roboto-Bold.woff2'); ?>) format('woff2');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            .wpvr-promo-banner * {
                box-sizing: border-box;
            }

            @keyframes arrowMove {
              0% { transform: translate(0, 0); }
              50% { transform: translate(18px, -18px); }
              55% { opacity: 0; visibility: hidden; transform: translate(-18px, 18px); }
              100% { opacity: 1; visibility: visible; transform: translate(0, 0); }
            }

            .wpvr-promo-banner {
                margin-top: 40px;
                padding: 14px 0;
                text-align: center;
                border-radius: 5px;
                border-left: 2px solid var(--vr-primary-color);
                background: #FFF;
                box-shadow: 0px 1px 1px 0px rgba(63, 4, 254, 0.10);
                width: calc(100% - 20px);
            }

            .wpvr-promo-banner__container {
                display: flex;
                margin: 0 auto;
                padding: 0 20px;
            }

            .wpvr-promo-banner__title {
                display: block;
                margin: 0 auto;
                text-align: center;
                font-size: 18px;
                line-height: normal;
            }

            .wpvr-promo-banner__link {
                position: relative;
                font-family: 'Roboto';
                font-size: 18px;
                font-style: normal;
                font-weight: 400;
                color: var(--vr-primary-color);
                transition: all .3s ease;
                text-decoration: none;
            }

            .wpvr-promo-banner__link:hover {
                color: var(--vr-primary-color);
            }

            .wpvr-promo-banner__link:focus {
                color: var(--vr-primary-color);
                box-shadow: none;
                outline: 0px solid transparent;
            }

            .wpvr-promo-banner__link::before {
                content: "";
                position: absolute;
                left: 0;
                bottom: 1px;
                width: calc(100% - 33px);
                height: 1px;
                background-color: var(--vr-primary-color);
                transform: scaleX(1);
                transform-origin: bottom left;
                transition: transform .4s ease;
            }

            .wpvr-promo-banner__link:hover::before {
                transform: scaleX(0);
                transform-origin: bottom right;
            }

            .wpvr-promo-banner__link:hover svg {
                animation: arrowMove .5s .4s linear forwards;
            }

            .wpvr-promo-banner__discount {
                font-weight: bold;
            }

            .wpvr-promo-banner__icon {
                display: inline-block;
                margin-left: 21px;
                vertical-align: middle;
                width: 12px;
                height: 17px;
                overflow: hidden;
                line-height: 1;
                position: relative;
                top: 1px;
            }

            .wpvr-promo-banner__svg {
                fill: none;
            }

            .wpvr-promo-banner__cross-icon {
                cursor: pointer;
                transition: all .3s ease;
            }

            .wpvr-promo-banner__cross-icon svg:hover path {
                stroke: var(--vr-primary-color);
            }
        </style>

        <?php

    }


    public function wpvr_sale_notification_notice()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpvr')) {
            wp_die(__('Permission check failed', 'wpvr'));
        }
        update_option('wpvr_sell_notification_bar', 'yes');
        echo json_encode(['success' => true,]);
        wp_die();
    }

}