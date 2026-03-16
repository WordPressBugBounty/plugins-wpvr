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

      if ( $screen->base === 'plugins' || $screen->base === 'dashboard' ) {
          if ( defined( 'REX_SPECIAL_OCCASION_BANNER_SHOWN_GLOBAL' ) ) {
              return;
          }
          define( 'REX_SPECIAL_OCCASION_BANNER_SHOWN_GLOBAL', true );
      }

      // Check if banner was dismissed within last 5 days
      $dismissed_option = 'wpvr_' . $this->occasion . '_dismissed';
      $dismissed_time = get_option($dismissed_option, 0);
      if ($dismissed_time && (time() - $dismissed_time) < 432000) {
          return; // Don't show if dismissed within last 5 days
      }

        $base_url = 'https://rextheme.com/wpvr/wpvr-pricing/';

        $utm_params = array(
            'utm_source'   => 'website',
            'utm_medium'   => 'plugin-ban-wpvr',
            'utm_campaign' => 'eidoffer2026',
        );

        $btn_link = add_query_arg( $utm_params, $base_url );

        $img_url  = WPVR_PLUGIN_DIR_URL . 'admin/icon/banner-images/ramadan-kareem.webp'; 
        $img_path = WPVR_PLUGIN_DIR_PATH . 'admin/icon/banner-images/ramadan-kareem.webp';
        $img_size = getimagesize($img_path);
        $img_width  = $img_size[0];
        $img_height = $img_size[1];

      ?>

        <section class="wpvr-promo-banner wpvr-promo-banner--regular" aria-labelledby="wpvr-promo-banner-title" id="wpvr-promo-banner">
             <div class="wpvr-regular-promotional-banner" id="wpvr-regular-promotional-banner" role="region" aria-labelledby="banner-flash-title">

                <div class="wpvr-regular-promotional-banner-container">

                    <div class="wpvr-regular-promotional-banner-content" id="banner-flash">

                        <!-- Close Button -->
                        <button class="wpvr-close-btn"
                                type="button"
                                aria-label="<?php esc_attr_e('Close banner', 'rex-product-feed'); ?>"
                                id="wpvr-promo-banner__cross-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9" fill="none">
                                <path d="M7.77482 0.75L0.75 7.75" stroke="#7E7E7E" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M7.77482 7.75L0.75 0.75" stroke="#7E7E7E" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>

                        <!-- Banner Title + Timer -->
                        <div class="wpvr-regular-promotional-banner-title">


                            <div class="wpvr-badge-content-img-area">
                                <div class="heart-icon">
                                    <figure class="wpvr-banner-img black-friday">
                                        <img src="<?php echo esc_url($img_url); ?>" alt="ramadan kareem"  width="<?php echo esc_attr($img_width); ?>"
                                        height="<?php echo esc_attr($img_height); ?>" />
                                    </figure>
                                </div>

                                <div class="wpvr-badge-content">

                                    <div class="wpvr-banner-title">
                                        

                                        <h2 id="banner-flash-title">
                                            <?php echo esc_html__('Eid Mubarak, Save Big', 'wpvr'); ?>
                                        </h2>
                                    </div>

                                    <div class="wpvr-title wpvr-banner-offer">
                                        <?php echo esc_html__('Up to 50% Off', 'wpvr'); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Countdown Timer -->
                            <div class="wpvr-timer">
                                <div class="wpvr-timer-box">
                                    <span class="wpvr-timer-number" id="wpvr_days">12</span>
                                    <span class="wpvr-timer-label">DAY</span>
                                </div>
                                <div class="wpvr-timer-box">
                                    <span class="wpvr-timer-number" id="wpvr_hours">10</span>
                                    <span class="wpvr-timer-label">HR</span>
                                </div>
                                <div class="wpvr-timer-box">
                                    <span class="wpvr-timer-number" id="wpvr_minutes">45</span>
                                    <span class="wpvr-timer-label">MIN</span>
                                </div>
                                <div class="wpvr-timer-box">
                                    <span class="wpvr-timer-number" id="wpvr_seconds">30</span>
                                    <span class="wpvr-timer-label">SEC</span>
                                </div>
                            </div>

                        </div>

                        <!-- CTA Button -->
                        <a href="<?php echo esc_url( $btn_link ); ?>"
                        target="_blank"
                        class="wpvr-regular-promotional-banner-link"
                        role="button"
                        aria-label="<?php esc_attr_e('Claim Your Deal on Eid-Ul-Fitr', 'wpvr'); ?>">
                            <?php esc_html_e('Claim Your Deal', 'wpvr'); ?>
                            <span class="arrow-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10">
                                    <path d="M10 0.78V9.22C10 9.65 9.65 10 9.22 10C8.79 10 8.44 9.65 8.44 9.22V2.66L1.33 9.77C1.19 9.92 0.99 10 0.78 10C0.35 10 0 9.65 0 9.22C0 9.01 0.08 8.81 0.23 8.67L7.33 1.56H0.78C0.35 1.56 0 1.21 0 0.78C0.35 1.56 0 1.21 0 0.78C0 0.35 0.35 0 0.78 0H9.22C9.65 0 10 0.35 10 0.78Z"
                                        fill="#fff"/>
                                </svg>
                            </span>
                        </a>

                    </div>
                </div>
            </div>

        </section>
        <script>

            (function () {
                // Get timer elements
                const daysEl = document.getElementById('wpvr_days');
                const hoursEl = document.getElementById('wpvr_hours');
                const minutesEl = document.getElementById('wpvr_minutes');
                const secondsEl = document.getElementById('wpvr_seconds');
                const banner = document.getElementById('wpvr-promo-banner');

                // Get labels (next siblings of timer numbers)
                const daysLabel = daysEl ? daysEl.nextElementSibling : null;
                const hoursLabel = hoursEl ? hoursEl.nextElementSibling : null;
                const minutesLabel = minutesEl ? minutesEl.nextElementSibling : null;
                const secondsLabel = secondsEl ? secondsEl.nextElementSibling : null;

                // Configure end time from PHP
                const wpvr_end = new Date(<?php echo json_encode($this->end_datetime); ?>);

                let wpvr_timer;

                // Update countdown timer
                function wpvr_updateCountdown() {
                    const now = new Date();

                    // Check if deal expired
                    if (now > wpvr_end) {
                        if (daysEl) daysEl.textContent = '0';
                        if (hoursEl) hoursEl.textContent = '0';
                        if (minutesEl) minutesEl.textContent = '0';
                        if (secondsEl) secondsEl.textContent = '0';
                        clearInterval(wpvr_timer);
                        // Auto-hide banner after countdown expires
                        setTimeout(function() {
                            if (banner) banner.style.display = 'none';
                        }, 2000);
                        return;
                    }

                    // Calculate remaining time
                    const diff = wpvr_end - now;
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    // Update numbers (pad single digits with leading zero)
                    if (daysEl) daysEl.textContent = days < 10 ? '0' + days : days;
                    if (hoursEl) hoursEl.textContent = hours < 10 ? '0' + hours : hours;
                    if (minutesEl) minutesEl.textContent = minutes < 10 ? '0' + minutes : minutes;
                    if (secondsEl) secondsEl.textContent = seconds < 10 ? '0' + seconds : seconds;

                    // Update labels (singular/plural)
                    if (daysLabel) daysLabel.textContent = (days === 0 || days === 1) ? 'DAY' : 'DAYS';
                    if (hoursLabel) hoursLabel.textContent = (hours === 0 || hours === 1) ? 'HR' : 'HRS';
                    if (minutesLabel) minutesLabel.textContent = (minutes === 0 || minutes === 1) ? 'MIN' : 'MINS';
                    if (secondsLabel) secondsLabel.textContent = (seconds === 0 || seconds === 1) ? 'SEC' : 'SECS';
                }

                // Initialize countdown
                wpvr_updateCountdown(); // Run immediately
                wpvr_timer = setInterval(wpvr_updateCountdown, 1000); // Update every second
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

            @font-face {
                font-family: 'Inter';
                src: url(<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/fonts/campaign-font/Inter-Bold.woff2'); ?>) format('woff2');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Inter';
                src: url(<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/fonts/campaign-font/Inter-SemiBold.woff2'); ?>) format('woff2');
                font-weight: 600;
                font-style: normal;
                font-display: swap;
            }

            .wpvr-regular-promotional-banner {
                background: radial-gradient(41.22% 84.27% at 50.55% 15.73%, #1d3a10 0, #0e1b09 100%);
                padding: 10px 0;
                position: relative;
                z-index: 2;
                margin-top: 40px;
                width: calc(100% - 20px);
            }

            .wpvr-regular-promotional-banner-container {
                max-width: 830px;
                margin: 0 auto;
                padding: 0 15px;
            }

            .wpvr-regular-promotional-banner-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
            }


        .wpvr-regular-promotional-banner-content .wpvr-badge-content-img-area {
            display: flex;
            align-items: center;
            gap: 33px;
        }

    .wpvr-regular-promotional-banner-content .wpvr-banner-title {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 3px;
        line-height: 1.1;
        animation: slideInLeft 0.8s ease-out;
    }

    .wpvr-regular-promotional-banner-content .heart-icon img {
        width: 100%;
        height: auto;
        max-width: 53px;
    }

    .wpvr-regular-promotional-banner-content .heart-icon figure {
        margin: 0;
    }

    .wpvr-regular-promotional-banner-content .wpvr-banner-title h2 {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 500;
        line-height: 1.3;
        letter-spacing: -0.32px;
        color: #FFF;
        margin: 0;
    }

    .wpvr-regular-promotional-banner-content .wpvr-regular-promotional-banner-title {
        display: flex;
        align-items: center;
        gap: 65px;
    }

    .wpvr-regular-promotional-banner-content .linno-banner.closing {
        animation: linno-slideUp 0.5s ease-in forwards;
    }

/* CLOSE BUTTON */
.wpvr-regular-promotional-banner-content .wpvr-close-btn {
    position: absolute;
    top: 33px;
    right: 40px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background-color: transparent;
    transition: all 0.3s ease-in-out;
}

.wpvr-regular-promotional-banner-content .wpvr-close-btn:hover {
    transform: rotate(90deg);
}

/* TITLE, SUBTITLE, BADGE */
.wpvr-regular-promotional-banner-content .wpvr-title {
    font-family: "Inter", sans-serif;
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -0.084px;
    color: #24ec2c;
    margin: 0;
}

.wpvr-regular-promotional-banner-content span.arrow-icon {
    margin-left: 10px;
}

.wpvr-regular-promotional-banner-content .wpvr-badge {
    font-family: "Inter", sans-serif;
    font-size: 16px;
    font-weight: 600;
    line-height: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #24EC2C;
}

/* BUTTON */
.wpvr-regular-promotional-banner-content .wpvr-regular-promotional-banner-link {
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    border-radius: 4px;
    background: #211cfd;
    color: #fff;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    font-weight: 600;
    line-height: 1;
    letter-spacing: -0.084px;
    text-decoration: none;
}

.wpvr-regular-promotional-banner-content .wpvr-regular-promotional-banner-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.3);
}

/* TIMER */
.wpvr-regular-promotional-banner-content .wpvr-timer {
    display: flex;
    gap: 3px;
}

.wpvr-regular-promotional-banner-content .wpvr-timer-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 6px 11px;
    text-align: center;
    color: #fff;
    border: 1px solid #2d5d1a;
    background: rgba(29, 58, 16, .4);
}

    .wpvr-regular-promotional-banner-content .wpvr-timer-box:first-child {
        border-radius: 4px 0 0 4px;
    }

    .wpvr-regular-promotional-banner-content .wpvr-timer-box:last-child {
        border-radius: 0 4px 4px 0;
    }

    .wpvr-regular-promotional-banner-content .wpvr-timer-number {
        font-family: "Inter", sans-serif;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 6px;
        color: #FFF;
    }

    .wpvr-regular-promotional-banner-content .wpvr-timer-label {
        font-family: "Inter", sans-serif;
        font-size: 12px;
        font-weight: 400;
        line-height: 1;
        letter-spacing: 0.24px;
        text-transform: uppercase;
        opacity: 0.8;
    }

    /* ANIMATIONS */
    @keyframes linno-slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes linno-slideUp {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(-100%); opacity: 0; }
    }

    @keyframes linno-pulse {
        0%,100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes linno-float {
        0%,100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    @keyframes heartbeat {
        0%,100% { transform: scale(1); }
        25% { transform: scale(1.2); }
        50% { transform: scale(1); }
    }

    /* REDUCED MOTION */
    @media (prefers-reduced-motion: reduce) {
        .wpvr-regular-promotional-banner {
            transition: none;
        }
    }

    /* RESPONSIVE */

    @media only screen and (max-width: 1199px) { 
        .wpvr-regular-promotional-banner {
            margin-top: 55px;
        }

        .wpvr-regular-promotional-banner-container {
            max-width: 780px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-regular-promotional-banner-title {
            gap: 40px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-close-btn {
            top: 32px;
            right: 14px;
        }
    }   

    @media only screen and (max-width: 991px) {

        .wpvr-regular-promotional-banner-container {
            max-width: 700px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-banner-title h2 {
            font-size: 13px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-timer-box {
            padding: 4px 9px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-badge-content-img-area {
            gap: 15px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-title {
            font-size: 20px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-timer-number {
            font-size: 18px;
            line-height: 1.3;
        }

        .wpvr-regular-promotional-banner-content .wpvr-close-btn {
            top: 32px;
            right: 3px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-badge {
            font-size: 14px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-regular-promotional-banner-title {
            gap: 30px;
        }
    }

    @media only screen and (max-width: 767px) {

        .wpvr-regular-promotional-banner-content {
            flex-direction: column;
            text-align: center;
            gap: 30px;
            padding: 30px 0;
        }

        .wpvr-regular-promotional-banner-content .heart-icon {
            margin-bottom: 0px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-title {
            font-size: 22px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-regular-promotional-banner-title {
            flex-direction: column;
        }

        .wpvr-regular-promotional-banner-content .wpvr-timer-number {
            font-size: 20px;
        }

        .wpvr-regular-promotional-banner-content .wpvr-timer {
            justify-content: center;
            flex-wrap: wrap;
        }

        .wpvr-regular-promotional-banner-content .wpvr-close-btn {
            top: 15px;
            right: 20px;
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
        
        // Store current timestamp for 24-hour dismissal
        $dismissed_option = 'wpvr_' . $this->occasion . '_dismissed';
        update_option($dismissed_option, time());
        
        echo json_encode(['success' => true,]);
        wp_die();
    }

}