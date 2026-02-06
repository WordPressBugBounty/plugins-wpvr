<?php

class WPVR_Notification_Bar {

  private $occasion   = '';
  private $start_datetime = '';
  private $end_datetime   = '';

  public function __construct( $occasion_slug, $start_datetime, $end_datetime ) {


    $this->occasion   = $occasion_slug;
    $this->start_datetime  = $start_datetime;
    $this->end_datetime    = $end_datetime;
    $current_datetime = current_time('Y-m-d H:i:s');

    if( $current_datetime >= $this->start_datetime && $current_datetime <= $this->end_datetime ) {
        // Hook into the admin_notices action to display the banner
        add_action('admin_notices', array($this, 'display_banner'));
        // Add styles
        add_action('admin_head', array($this, 'add_styles'));

        add_action('wp_ajax_wpvr_sale_notification_notice',  array($this, 'wpvr_sale_notification_notice'));
    }
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

        $img_url  = WPVR_PLUGIN_DIR_URL . 'admin/icon/banner-images/happy-new-year.webp'; 
        $img_path = WPVR_PLUGIN_DIR_PATH . 'admin/icon/banner-images/happy-new-year.webp';
        $img_size = getimagesize($img_path);
        $img_width  = $img_size[0];
        $img_height = $img_size[1];

      ?>

        <section class="wpvr-promo-banner wpvr-promo-banner--regular" aria-labelledby="wpvr-promo-banner-title" id="wpvr-promo-banner">
            <div class="wpvr-promo-banner__container">
                <div class="wpvr-halloween-promotional-banner-content">
                    <div class="wpvr-banner-title">

                        <div class="wpvr-spooktacular">
                            <span><?php echo esc_html__('New Year Savings.', 'wpvr'); ?></span>
                        </div>

                        <!-- Black Friday Logo -->
                        <figure class="wpvr-banner-img black-friday">
                            <img src="<?php echo esc_url($img_url); ?>" alt="Happy New Year 2025 Sale"  width="<?php echo esc_attr($img_width); ?>"
                             height="<?php echo esc_attr($img_height); ?>" />
                            <figcaption class="visually-hidden">Happy New Year 2025 Logo</figcaption>
                        </figure>

                        <div class="wpvr-discount-text">
                            <?php echo esc_html__('Get ', 'wpvr'); ?>
                            <span class="wpvr-halloween-percentage"><?php echo esc_html__('25% OFF ', 'wpvr'); ?></span>
                            <?php echo esc_html__('on ', 'wpvr'); ?>
                            <span class="wpvr-text-highlight">
                                <?php echo esc_html__('WPVR!', 'wpvr'); ?>
                            </span>
                        </div>

                        <!-- Countdown -->
                        <div id="wpvr_bf_countdown-banner">
                            <span id="wpvr_bf_countdown-text"></span>
                        </div>

                    </div>

                    <a href="<?php echo esc_url($btn_link); ?>"
                    target="_blank"
                    class="wpvr-halloween-banner-link"
                    aria-label="<?php echo esc_attr__('Get 25% OFF on WPVR Pro', 'wpvr'); ?>">
                        <?php echo esc_html__('Get 25% OFF', 'wpvr'); ?>
                        <span class="wpvr-arrow-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <path d="M9.71875 0.25C9.99225 0.25 10.2548 0.358366 10.4482 0.551758C10.6416 0.745155 10.75 1.00775 10.75 1.28125V9.71875C10.75 9.99225 10.6416 10.2548 10.4482 10.4482C10.2548 10.6416 9.99225 10.75 9.71875 10.75C9.44525 10.75 9.18265 10.6416 8.98926 10.4482C8.79587 10.2548 8.6875 9.99225 8.6875 9.71875V3.77051L2.01074 10.4482C1.81734 10.6416 1.55476 10.75 1.28125 10.75C1.00775 10.75 0.745155 10.6416 0.551758 10.4482C0.358365 10.2548 0.25 9.99225 0.25 9.71875C0.250003 9.44525 0.358362 9.18265 0.551758 8.98926L7.22949 2.3125H1.28125C1.00775 2.3125 0.745151 2.20414 0.551758 2.01074C0.358366 1.81735 0.25 1.55475 0.25 1.28125C0.25 1.00775 0.358366 0.745154 0.551758 0.551758C0.745151 0.358365 1.00775 0.250004 1.28125 0.25H9.71875Z" fill="#00B4FF" stroke="#00B4FF" stroke-width="0.5"/>
                            </svg>
                        </span>
                    </a>
                </div>


                <a class="wpvr-promo-banner__cross-icon" type="button" aria-label="close banner" id="wpvr-promo-banner__cross-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M11 1L1 11" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1 1L11 11" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

            </div>

        </section>
        <script>

            (function () {
                const wpvr_bf_text = document.getElementById("wpvr_bf_countdown-text");

                // === Configure start & end times ===
                const wpvr_bf_start = new Date("2025-12-31T00:00:00"); // Deal start date
                const wpvr_bf_end = new Date("2026-01-12T23:59:59");   // Deal end date

                // === Update countdown text ===
                function wpvr_bf_updateCountdown() {
                const now = new Date();

                // Before deal starts
                if (now < wpvr_bf_start) {
                    wpvr_bf_text.textContent = "Deal coming soon!";
                    return;
                }

                // After deal ends
                if (now > wpvr_bf_end) {
                    wpvr_bf_text.textContent = "Deal expired.";
                    clearInterval(wpvr_bf_timer);
                    return;
                }

                // Calculate remaining time
                const diff = wpvr_bf_end - now;
                const minutes = Math.floor(diff / (1000 * 60));
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));

                    // Display message with <span> for styling numbers
                    if (days > 1) {
                        wpvr_bf_text.innerHTML = `<span>${days}</span> days left.`;
                    } else if (days === 1) {
                        wpvr_bf_text.innerHTML = `<span>1</span> day left.`;
                    } else if (hours >= 1) {
                        wpvr_bf_text.innerHTML = `<span>${hours}</span> hrs left.`;
                    } else if (minutes >= 1) {
                        wpvr_bf_text.innerHTML = `<span>${minutes}</span> mins left.`;
                    } else {
                        wpvr_bf_text.innerHTML = "Deal expired.";
                        clearInterval(wpvr_bf_timer);
                    }
                }

                // === Initialize countdown ===
                wpvr_bf_updateCountdown(); // Run immediately
                const wpvr_bf_timer = setInterval(wpvr_bf_updateCountdown, 30000); // Update every 30s
            })();

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
              --wpvr-primary-color: #24EC2C;
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

            .wpvr-spooktacular span {
                font-weight: 900;
            }

            @keyframes arrowMove {
              0% { transform: translate(0, 0); }
              50% { transform: translate(18px, -18px); }
              55% { opacity: 0; visibility: hidden; transform: translate(-18px, 18px); }
              100% { opacity: 1; visibility: visible; transform: translate(0, 0); }
            }

            .wpvr-promo-banner {
                margin-top: 40px;
                padding: 17px 0;
                text-align: center;
                background: linear-gradient(90deg, #24EC2C 0%, #2022F8 16.24%, #1A1B9D 51.84%, #2022F8 99.14%);
                width: calc(100% - 20px);
            }
            

            .wpvr-promo-banner__container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 0 auto;
                padding: 0 20px;
                width: 100%;
            }

            .wpvr-banner-img  {
                margin: 0;
            }

            .wpvr-banner-img img {
                max-width: 150px;
                height: auto;
            }

            .wpvr-discount-text{
                font-weight: 600;
            }

            .wpvr-halloween-promotional-banner-content .visually-hidden {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                border: 0;
            }

            .wpvr-halloween-promotional-banner-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                max-width: 1090px;
                margin: 0 auto;
                width: 100%;
            }

            .wpvr-halloween-promotional-banner-content .wpvr-banner-title {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 20px;
                color: #FFF;
                font-size: 16px;
                font-weight: 500;
                line-height: 1;
                text-transform: capitalize;
            }

            .wpvr-halloween-promotional-banner-content span.wpvr-halloween-highlight {
                font-size: 16px;
                font-weight: 900;
                color: #24EC2C;
            }

            .wpvr-halloween-percentage {
                font-size: 16px;
                font-weight: 900;
                color: #24EC2C;
            }

            .wpvr-text-highlight {
                font-size: 16px;
                font-weight: 700;
                color: #fff;
            }

            .wpvr-halloween-banner-link {
                position: relative;
                font-family: 'Roboto';
                font-size: 15px;
                font-weight: 800;
                color: var(--wpvr-primary-color);
                transition: all .3s ease;
                text-decoration: none;
                letter-spacing: -0.084px;
            }

            .wpvr-halloween-banner-link:hover {
                color: var(--wpvr-primary-color);
            }

            .wpvr-halloween-banner-link:focus {
                color: var(--wpvr-primary-color);
                box-shadow: none;
                outline: 0px solid transparent;
            }

            .wpvr-halloween-banner-link::before {
                content: "";
                position: absolute;
                left: 0;
                bottom: 1px;
                width: 100%;
                height: 2px;
                background-color: var(--wpvr-primary-color);
                transform: scaleX(1);
                transform-origin: bottom left;
                transition: transform .4s ease;
            }

            .wpvr-halloween-banner-link:hover::before {
                transform: scaleX(0);
                transform-origin: bottom right;
            }

            .wpvr-halloween-banner-link:hover svg {
                animation: arrowMove .5s .4s linear forwards;
            }

            .wpvr-arrow-icon {
                display: inline-block;
                margin-left: 8px;
                vertical-align: middle;
                width: 12px;
                height: 17px;
                overflow: hidden;
                line-height: 1;
                position: relative;
                top: 1px;
            }

            .wpvr-arrow-icon svg path {
                fill: var(--wpvr-primary-color);
            }

            #wpvr_bf_countdown-text {
                font-weight: 500;
                text-transform: capitalize;
            }

            #wpvr_bf_countdown-text  span {
                color: #24ec2c;
                font-weight: 900;
            }

            .wpvr-promo-banner__svg {
                fill: none;
            }

            .wpvr-promo-banner__cross-icon {
                cursor: pointer;
                transition: all .3s ease;
            }

            .wpvr-promo-banner__cross-icon svg:hover path {
                stroke: var(--wpvr-primary-color);
            }

            @media only screen and (max-width: 1399px) {
                .wpvr-promo-banner__cross-icon {
                    margin-left: 10px;
                }
            }


            @media only screen and (max-width: 1199px) {

                .wpvr-text-highlight,
                .wpvr-halloween-promotional-banner-content .wpvr-banner-title {
                    font-size:15px;
                }

                .wpvr-spooktacular {
                    max-width: 102px;
                    line-height: 1.2;
                }

                .wpvr-regular-promotional-banner .regular-promotional-banner-content img {
                    max-width: 115px;
                }

                .wpvr-discount-text {
                    max-width: 186px;
                    line-height: 1.2;
                }

                .wpvr-halloween-promotional-banner-content span.wpvr-halloween-highlight {
                    font-size: 16px;
                }

                .wpvr-banner-img img {
                    max-width: 130px;
                }

                .wpvr-halloween-percentage {
                    font-size: 16px;
                }

                .wpvr-halloween-promotional-banner-content {
                    max-width: 720px;
                }

                .wpvr-halloween-banner-link {
                    font-size: 14px;
                }

            }

            @media only screen and (max-width: 991px) {
                .wpvr-promo-banner__container {
                    padding: 0px 10px;
                }

                .wpvr-promo-banner {
                    margin-top: 66px;
                    padding: 15px 0;
                }

                .wpvr-banner-img img {
                    max-width: 115px;
                }

                .wpvr-arrow-icon {
                    margin-left: 5px;
                }
            }


            @media only screen and (max-width: 767px) {

                .wpvr-promo-banner__container {
                    align-items: flex-start;
                }

                .wpvr-halloween-promotional-banner-content .wpvr-banner-title {
                    flex-direction: column;
                    gap: 0;
                }

                .wpvr-halloween-promotional-banner-content {
                   flex-direction: column;
                }
            }


        </style>

        <?php

    }


    public function wpvr_sale_notification_notice()
    {
        if (!current_user_can('manage_options')) {
            $response = array(
                'success'   => false,
                'data'  => 'Permission denied.'
            );
            wp_send_json($response);
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpvr')) {
            wp_die(__('Permission check failed', 'wpvr'));
        }
        update_option('wpvr_sell_new_year_notification_bar', 'yes');
        echo json_encode(['success' => true,]);
        wp_die();
    }

}