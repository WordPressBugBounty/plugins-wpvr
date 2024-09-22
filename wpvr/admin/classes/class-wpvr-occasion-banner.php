<?php

/**
 * SpecialOccasionBanner Class
 *
 * This class is responsible for displaying a special occasion banner in the WordPress admin.
 *
 */
class WPVR_Special_Occasion_Banner
{

    /**
     * The occasion identifier.
     *
     * @var string
     */
    private $occasion;

    /**
     * The start date and time for displaying the banner.
     *
     * @var int
     */
    private $start_date;

    /**
     * The end date and time for displaying the banner.
     *
     * @var int
     */
    private $end_date;

    /**
     * Constructor method for SpecialOccasionBanner class.
     *
     * @param string $occasion   The occasion identifier.
     * @param string $start_date The start date and time for displaying the banner.
     * @param string $end_date   The end date and time for displaying the banner.
     */
    public function __construct($occasion, $start_date, $end_date)
    {
        $this->occasion     = $occasion;
        $this->start_date   = strtotime($start_date);
        $this->end_date     = strtotime($end_date);

        if (!defined('WPVR_PRO_VERSION') && 'no' === get_option('_wpvr_eid_al_adha_2024', 'no')) {
            //        if ( 'no' === get_option( '_wpvr_eid_al_adha_2024', 'no' )) {

            // Hook into the admin_notices action to display the banner
            add_action('admin_notices', array($this, 'display_banner'));

            // Add styles
            add_action('admin_head', array($this, 'add_styles'));
        }
    }


    /**
     * Displays the special occasion banner if the current date and time are within the specified range.
     */
    public function display_banner()
    {

        $screen                     = get_current_screen();
        $promotional_notice_pages   = ['dashboard', 'plugins', 'edit-wpvr_item', 'toplevel_page_wpvr', 'wp-vr_page_wpvr-setup-wizard', 'wpvr_item', 'wp-vr_page_wpvr-addons'];
        $current_date_time          = current_time('timestamp');

        if (!in_array($screen->id, $promotional_notice_pages)) {
            return;
        }

        if ($current_date_time < $this->start_date || $current_date_time > $this->end_date) {
            return;
        }
        // Calculate the time remaining in seconds
        $time_remaining = $this->end_date - $current_date_time;
        $btn_link = 'https://rextheme.com/wpvr/wpvr-pricing/?utm_source=plugin-notification-CTA&utm_medium=VR-plugin&utm_campaign=eid-ul-adha-24';
?>

        <div class="rex-feed-tb__notification" id="rex_wpvr_deal_notification">

            <div class="banner-overflow">
                <div class="wpvr-occasion-anniv__container-area">

                    <div class="wpvr-occasion-anniv__image wpvr-occasion-anniv__image--left">
                        <figure>
                            <img src="<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/icon/eid-ul-adha/moon.webp'); ?>" alt="Eid Ul Adha" />
                        </figure>
                    </div>

                    <div class="wpvr-occasion-anniv__content-area">


                        <div class="wpvr-occasion-anniv__image--group">

                            <div class='wpvr-occasion-anniv__image wpvr-occasion-anniv__image--eid-mubarak'>
                                <figure>
                                    <img src="<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/icon/eid-ul-adha/eid-mubarak.webp'); ?>" alt="Eid Ul Adha" />
                                </figure>
                            </div>

                            <div class='wpvr-occasion-anniv__image wpvr-occasion-anniv__image--cartlift-logo'>
                                <figure>
                                    <img src="<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/icon/eid-ul-adha/wpvr-logo.webp'); ?>" alt="WPVR Logo" />
                                </figure>
                            </div>

                            <div class="wpvr-occasion-anniv__image wpvr-occasion-anniv__image--four">
                                <figure>
                                    <img src="<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/icon/eid-ul-adha/discount.webp'); ?>" alt="20% discount" />
                                </figure>
                            </div>



                            <div class="wpvr-occasion-anniv__text-divider">

                                <div class="wpvr-occasion-anniv__lead-text">
                                    <span>


                                        <svg width="33" height="30" viewBox="0 0 33 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M28.5843 25.483C28.501 25.2436 28.4077 24.9778 28.3093 24.698C28.2285 24.4683 28.1442 24.2291 28.0591 23.9878C27.7788 23.1927 27.4901 22.3739 27.2901 21.7891C27.2416 21.6548 27.1932 21.4493 27.2064 21.2368C27.2195 21.0257 27.3117 20.6666 27.6929 20.511C27.8137 20.461 27.9551 20.4291 28.109 20.4473C28.2673 20.4661 28.394 20.5326 28.4898 20.6083C28.6275 20.7171 28.7368 20.8821 28.7977 20.9741C28.8056 20.9859 28.8126 20.9966 28.8188 21.0057L28.8215 21.0098C28.9485 21.2012 29.0236 21.365 29.0856 21.5004C29.0999 21.5317 29.1135 21.5614 29.1269 21.5896C29.3163 21.9814 29.507 22.3733 29.698 22.7656C30.2 23.797 30.7035 24.8314 31.1878 25.8761C31.3812 26.2879 31.4945 26.7351 31.5924 27.1212C31.6015 27.1571 31.6105 27.1926 31.6193 27.2274L31.6194 27.2276C31.696 27.528 31.7123 27.8965 31.4914 28.204C31.2669 28.5166 30.9041 28.6193 30.5664 28.6328L30.5664 28.6328C29.4158 28.6789 28.4257 28.6798 27.1353 28.6554L27.135 28.6554C27.0797 28.6543 27.0243 28.6532 26.9689 28.6521C25.5744 28.6251 24.1286 28.597 22.7007 28.3621L22.6982 28.3617C22.3862 28.3087 22.1239 28.2242 21.9181 28.0634C21.7013 27.894 21.607 27.6854 21.5466 27.5404L21.5366 27.5164L21.5292 27.4915C21.4935 27.3722 21.4859 27.2395 21.5231 27.1057C21.5595 26.9741 21.6306 26.8725 21.7045 26.7985C21.8409 26.6619 22.0133 26.5927 22.1325 26.5546C22.3802 26.4754 22.6698 26.4496 22.8187 26.4375C23.2676 26.3962 23.5275 26.4144 23.8577 26.438C24.6241 26.4893 25.3861 26.5417 26.1547 26.5946C26.2079 26.5982 26.2611 26.6019 26.3143 26.6055C21.2778 24.2043 16.4773 21.3745 12.3077 17.5222C7.96169 13.5076 4.20576 9.0052 1.53129 3.65248L1.53127 3.65249L1.52899 3.6478L1.5094 3.60758C1.32594 3.2309 1.11181 2.79125 0.992031 2.3247C0.990165 2.31792 0.987843 2.30983 0.985182 2.30056C0.96012 2.21328 0.904934 2.0211 0.916621 1.82238C0.932677 1.54937 1.06105 1.29594 1.31824 1.09408L1.34469 1.07332L1.37368 1.05629C1.4917 0.986953 1.63314 0.944466 1.78929 0.95393C1.94062 0.963102 2.06768 1.01872 2.16618 1.08233C2.34667 1.19888 2.49127 1.38215 2.60816 1.55265C2.77767 1.79283 2.95768 2.05809 3.11483 2.33864L3.11638 2.34145C5.51596 6.69088 8.52014 10.5846 12.0092 14.1314L11.6656 14.4694L12.0092 14.1314C16.3991 18.5941 21.6387 21.8661 27.1684 24.7864C27.6316 25.0281 28.0982 25.2525 28.5843 25.483Z" fill="#00B4FF" stroke="#00B4FF" />
                                        </svg>


                                    </span>

                                    <h2>
                                        <?php echo __("Ends <br> Soon", 'rextheme') ?>
                                    </h2>

                                </div>

                            </div>

                        </div>

                        <!-- .wpvr-occasion-anniv__image end -->
                        <div class="wpvr-occasion-anniv__btn-area">

                            <a href="<?php echo esc_url($btn_link); ?>" role="button" class="wpvr-occasion-anniv__btn" target="_blank">
                                <?php echo __('Claim Offer Now', 'rextheme') ?>
                            </a>
                            <svg width="70" height="63" fill="none" viewBox="0 0 70 63" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#FF44BC" d="M4.607 7.083c1.027-1.909 1.655-3.536 1.59-5.337a5.106 5.106 0 00-3.25-.907A9.527 9.527 0 011.34 6.08c1.486-.104 2.372.062 3.267 1.002z" />
                                <path fill="url(#paint0_linear_2007_3)" d="M67.79 55.948c-1.79-.111-3.545-.96-6.504-4.16a9.216 9.216 0 00-1.916 5.447 16.383 16.383 0 007.3 3.89 8.389 8.389 0 011.12-5.177z" />
                                <path fill="#EE8134" d="M60.724 14.364a18.229 18.229 0 01-6.33 3.313 5.826 5.826 0 001.984 3.154 24.284 24.284 0 006.717-2.97c-1.715-1.08-2.408-1.907-2.37-3.497z" />
                                <defs>
                                    <linearGradient id="paint0_linear_2007_3" x1="27276.4" x2="27248.7" y1="7756.52" y2="7812.21" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#4D8EFF" />
                                        <stop offset=".43" stop-color="#3F76FF" />
                                        <stop offset="1" stop-color="#2850FF" />
                                    </linearGradient>
                                </defs>
                            </svg>

                        </div>

                    </div>

                    <div class="wpvr-occasion-anniv__image wpvr-occasion-anniv__image--right">
                        <figure>
                            <img src="<?php echo esc_url(WPVR_PLUGIN_DIR_URL . 'admin/icon/eid-ul-adha/mosque.webp'); ?>" alt="Masjid" />
                        </figure>
                    </div>

                </div>

            </div>

            <!--            <div class="rex-feed-tb__cross-top" id="rex_deal_close">-->
            <!--                <img src="--><?php //echo  plugin_dir_url(__FILE__) . 'images/cross-top.svg'; 
                                                ?><!--" />-->
            <!---->
            <!--            </div>-->
            <a class="close-promotional-banner wpvr-black-friday-close-promotional-banner rex-feed-tb__cross-top" type="button" aria-label="close banner" id="wpvr-black-friday-close-button">
                <svg width="12" height="13" fill="none" viewBox="0 0 12 13" xmlns="http://www.w3.org/2000/svg">
                    <path stroke="#7A8B9A" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 1.97L1 11.96m0-9.99l10 9.99" />
                </svg>
            </a>

        </div>
        <!-- .rex-feed-tb-notification end -->

        <script>
            rexfeed_deal_countdown_handler();
            /**
             * Handles count down on deal notice
             *
             * @since 7.3.18
             */
            function rexfeed_deal_countdown_handler() {
                // Pass the calculated time remaining to JavaScript
                let timeRemaining = <?php echo $time_remaining; ?>;

                // Update the countdown every second
                setInterval(function() {
                    const daysElement = document.getElementById('rex-feed-tb__days');
                    const hoursElement = document.getElementById('rex-feed-tb__hours');
                    const minutesElement = document.getElementById('rex-feed-tb__mins');
                    //const secondsElement = document.getElementById('seconds');

                    timeRemaining--;

                    if (daysElement && hoursElement && minutesElement) {
                        // Decrease the remaining time

                        // Calculate new days, hours, minutes, and seconds
                        let days = Math.floor(timeRemaining / (60 * 60 * 24));
                        let hours = Math.floor((timeRemaining % (60 * 60 * 24)) / (60 * 60));
                        let minutes = Math.floor((timeRemaining % (60 * 60)) / 60);
                        //let seconds = timeRemaining % 60;

                        // Format values with leading zeros
                        days = (days < 10) ? '0' + days : days;
                        hours = (hours < 10) ? '0' + hours : hours;
                        minutes = (minutes < 10) ? '0' + minutes : minutes;
                        //seconds = (seconds < 10) ? '0' + seconds : seconds;

                        // Update the HTML
                        daysElement.textContent = days;
                        hoursElement.textContent = hours;
                        minutesElement.textContent = minutes;
                    }
                    // Check if the countdown has ended
                    if (timeRemaining <= 0) {
                        // rexfeed_hide_deal_notice();
                    }
                }, 1000); // Update every second
            }

            document.getElementById('rex_deal_close').addEventListener('click', rexfeed_hide_deal_notice);

            /**
             * Hide deal notice and save parameter to keep it hidden for future
             *
             * @since 7.3.2
             */
            function rexfeed_hide_deal_notice() {
                document.getElementById('rex_deal_notification').style.display = 'none';
                const payload = {
                    occasion: document.getElementById('rexfeed_special_occasion')?.value
                }

                wpAjaxHelperRequest('rex-feed-hide-deal-notice', payload);
            }
        </script>
    <?php
    }


    /**
     * Adds internal CSS styles for the special occasion banners.
     */
    public function add_styles()
    {
        $plugin_dir_url = plugin_dir_url(__FILE__);
    ?>
        <style id="wpvr-promotional-banner-style" type="text/css">
            /* notification var css */

            @font-face {
                font-family: 'Lexend Deca';
                src: url(<?php echo WPVR_PLUGIN_DIR_URL . 'admin/fonts/campaign-font/LexendDeca-SemiBold.woff2'; ?>) format('woff2'),
                    url(<?php echo WPVR_PLUGIN_DIR_URL . 'admin/fonts/campaign-font/LexendDeca-SemiBold.woff'; ?>) format('woff');
                font-weight: 600;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Lexend Deca';
                src: url(<?php echo WPVR_PLUGIN_DIR_URL . 'admin/fonts/campaign-font/LexendDeca-Bold.woff2'; ?>) format('woff2'),
                    url(<?php echo WPVR_PLUGIN_DIR_URL . 'admin/fonts/campaign-font/LexendDeca-Bold.woff'; ?>) format('woff');
                font-weight: bold;
                font-style: normal;
                font-display: swap;
            }


            .rex-feed-tb__notification,
            .rex-feed-tb__notification * {
                box-sizing: border-box;
            }

            .rex-feed-tb__notification {
                background-color: #d6e4ff;
                width: calc(100% - 20px);
                margin: 50px 0 20px;
                background-image: url(<?php echo WPVR_PLUGIN_DIR_URL . 'admin/icon/eid-ul-adha/notification-bg.webp'; ?>);
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                position: relative;
                border: none;
                box-shadow: none;
                display: block;
                max-height: 110px;
            }

            .rex-feed-tb__notification .banner-overflow {
                overflow: hidden;
                position: relative;
                width: 100%;
            }

            .rex-feed-tb__notification .rex-feed-tb__cross-top {
                position: absolute;
                top: -10px;
                right: -9px;
                background: #fff;
                border: none;
                padding: 0;
                border-radius: 50%;
                cursor: pointer;
                z-index: 9;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .rex-feed-tb__notification .rex-feed-tb__cross-top img {
                width: 22px;
            }

            .rex-feed-tb__notification .rex-feed-tb__cross-top svg {
                display: block;
                width: 15px;
                height: 15px;
            }

            .wpvr-occasion-anniv__container {
                width: 100%;
                margin: 0 auto;
                max-width: 1640px;
                position: relative;
                padding-right: 15px;
                padding-left: 15px;
            }

            .wpvr-occasion-anniv__container-area {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .wpvr-occasion-anniv__content-area {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-evenly;
                max-width: 1310px;
                position: relative;
                padding-right: 15px;
                padding-left: 15px;
                margin: 0 auto;
                z-index: 1;
            }

            .wpvr-occasion-anniv__image--left {
                position: absolute;
                left: 110px;
                top: 50%;
                transform: translateY(-50%);
            }

            .wpvr-occasion-anniv__image--right {
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
            }

            .wpvr-occasion-anniv__image--group {
                display: flex;
                align-items: center;
                gap: 50px;
            }

            .wpvr-occasion-anniv__image--left img {
                width: 100%;
                max-width: 108px;
            }

            .wpvr-occasion-anniv__image--eid-mubarak img {
                width: 100%;
                max-width: 140px;
            }

            .wpvr-occasion-anniv__image--cartlift-logo img {
                width: 100%;
                max-width: 143px;
            }

            .wpvr-occasion-anniv__image--four img {
                width: 100%;
                max-width: 264px;
            }

            .wpvr-occasion-anniv__lead-text {
                display: flex;
                gap: 11px;
            }

            .wpvr-occasion-anniv__lead-text h2 {
                font-size: 42px;
                line-height: 1;
                margin: 0;
                color: #3F04FE;
                font-weight: 700;
                font-family: 'Lexend Deca';

            }



            .wpvr-occasion-anniv__image--right img {
                width: 100%;
                max-width: 152px;
            }

            .wpvr-occasion-anniv__image figure {
                margin: 0;
            }

            .wpvr-occasion-anniv__text-container {
                position: relative;
                max-width: 330px;
            }

            .wpvr-occasion-anniv__campaign-text-images {
                position: absolute;
                top: -10px;
                right: -15px;
                max-width: 100%;
                max-height: 24px;
            }



            .wpvr-occasion-anniv__btn-area {
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
                position: relative;
            }

            .wpvr-occasion-anniv__btn-area svg {
                position: absolute;
                width: 70px;
                right: -20px;
                top: -15px;
            }

            .wpvr-occasion-anniv__btn {
                font-family: 'Lexend Deca';
                font-size: 18px;
                font-style: normal;
                font-weight: 500;
                line-height: 1;
                text-align: center;
                border-radius: 30px;
                background: -webkit-gradient(linear, left bottom, left top, from(#8965FF), to(#3F04FE));
                background: -moz-linear-gradient(bottom, #8965FF 11.67%, #3F04FE 100%);
                background: -o-linear-gradient(180deg, #8965FF 11.67%, #3F04FE 100%);
                background: linear-gradient(180deg, #8965FF 11.67%, #3F04FE 100%);
                box-shadow: 0px 10px 30px rgba(12, 10, 81, 0.25);
                color: #fff;
                padding: 17px 26px;
                display: inline-block;
                text-decoration: none;
                cursor: pointer;
                text-transform: capitalize;
                -webkit-transition: all .5s linear;
                -o-transition: all .5s linear;
                -moz-transition: all .5s linear;
                transition: all .5s linear;
            }

            a.wpvr-occasion-anniv__btn:hover {
                color: #3F04FE;
                background: linear-gradient(0deg, #ffffff 100%, #fff 0);
            }

            .wpvr-occasion-anniv__btn-area a:focus {
                color: #fff;
                box-shadow: none;
                outline: 0px solid transparent;
            }

            .wpvr-occasion-anniv__btn:hover {
                background-color: #201cfe;
                color: #fff;
            }

            .wpcartlift-banner-title p {
                margin: 0;
                font-weight: 700;
                max-width: 315px;
                font-size: 24px;
                color: #ffffff;
                line-height: 1.3;
            }

            @media only screen and (min-width: 1921px) {
                .wpvr-occasion-anniv__image--left img {
                    max-width: 108px;
                }
            }


            @media only screen and (max-width: 1710px) {

                .wpvr-occasion-anniv__image--left {
                    left: 90px;
                }

                .wpvr-occasion-anniv__lead-text h2 {
                    font-size: 36px;
                }

                .wpvr-occasion-anniv__content-area {
                    justify-content: center;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 30px;
                }

                .wpvr-occasion-anniv__content-area {
                    gap: 30px;
                }

                .wpvr-occasion-anniv__btn {
                    font-size: 18px;
                }

                .wpvr-occasion-anniv__btn-area svg {
                    position: absolute;
                    width: 70px;
                    right: -20px;
                    top: -15px;
                }

            }


            @media only screen and (max-width: 1440px) {

                .rex-feed-tb__notification {
                    max-height: 99px;
                }

                .wpvr-occasion-anniv__image--left {
                    left: 20px;
                }

                .wpvr-occasion-anniv__image--left img {
                    width: 90%;
                }

                .wpvr-occasion-anniv__image--eid-mubarak img {
                    width: 90%;
                }

                .wpvr-occasion-anniv__image--cartlift-logo img {
                    width: 90%;
                }

                .wpvr-occasion-anniv__image--four img {
                    width: 90%;
                }

                .wpvr-occasion-anniv__image--right img {
                    width: 90%;
                }

                .wpvr-occasion-anniv__lead-text h2 {
                    font-size: 28px;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 25px;
                }

                .wpvr-occasion-anniv__content-area {
                    gap: 30px;
                    justify-content: center;
                }

                .wpvr-occasion-anniv__btn {
                    font-size: 16px;
                    font-weight: 400;
                    border-radius: 30px;
                    padding: 12px 16px;
                }

                .wpvr-occasion-anniv__btn-area svg {
                    position: absolute;
                    width: 60px;
                    right: -15px;
                    top: -15px;
                }

            }


            @media only screen and (max-width: 1399px) {

                .rex-feed-tb__notification {
                    max-height: 79px;
                }

                .wpvr-occasion-anniv__image--left {
                    left: 20px;
                }

                .wpvr-occasion-anniv__image--left img {
                    max-width: 85.39px;
                }

                .wpvr-occasion-anniv__image--eid-mubarak img {
                    max-width: 105px;
                }

                .wpvr-occasion-anniv__image--cartlift-logo img {
                    max-width: 114px;
                }

                .wpvr-occasion-anniv__image--four img {
                    max-width: 210px;
                }

                .wpvr-occasion-anniv__image--right img {
                    max-width: 121.5px;
                }

                .wpvr-occasion-anniv__lead-text h2 {
                    font-size: 24px;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 20px;
                }

                .wpvr-occasion-anniv__content-area {
                    gap: 35px;
                }

                .wpvr-occasion-anniv__btn {
                    font-size: 14px;
                    font-weight: 600;
                    border-radius: 30px;
                    padding: 12px 16px;
                }

                .wpvr-occasion-anniv__btn-area svg {
                    width: 45px;
                    right: -13px;
                    top: -21px;
                }

            }

            @media only screen and (max-width: 1024px) {
                .rex-feed-tb__notification {
                    max-height: 75px;
                }

                .wpvr-occasion-anniv__image--left img {
                    max-width: 76.39px;
                }

                .wpvr-occasion-anniv__image--eid-mubarak img {
                    max-width: 105px;
                }

                .wpvr-occasion-anniv__image--cartlift-logo img {
                    max-width: 107px;
                }

                .wpvr-occasion-anniv__image--four img {
                    max-width: 197px;
                }

                .wpvr-occasion-anniv__image--right img {
                    max-width: 111.5px;
                }

                .wpvr-occasion-anniv__lead-text h2 {
                    font-size: 22px;
                }

                .wpvr-occasion-anniv__lead-text svg {
                    width: 25px;
                    margin-top: -10px;
                }


                .wpvr-occasion-anniv__content-area {
                    gap: 30px;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 15px;
                }

                .wpvr-occasion-anniv__btn {
                    font-size: 12px;
                    line-height: 1.2;
                    padding: 11px 12px;
                    font-weight: 400;
                }

                .wpvr-occasion-anniv__btn {
                    box-shadow: none;
                }

                .wpvr-occasion-anniv__image--right,
                .wpvr-occasion-anniv__image--left {
                    display: none;
                }

                .wpvr-occasion-anniv__btn-area svg {
                    width: 40px;
                    right: -15px;
                    top: -23px;
                }


            }

            @media only screen and (max-width: 768px) {

                .rex-feed-tb__notification {
                    margin: 60px 0 20px;
                }

                .wpvr-occasion-anniv__container-area {
                    padding: 0 15px;
                }

                .wpvr-occasion-anniv__container-area {
                    justify-content: center;
                    gap: 20px;
                }

                .rex-feed-tb__notification {
                    max-height: 64px;
                }

                .wpvr-occasion-anniv__image--left img {
                    max-width: 76.39px;
                }

                .wpvr-occasion-anniv__image--eid-mubarak img {
                    max-width: 92px;
                }

                .wpvr-occasion-anniv__image--cartlift-logo img {
                    max-width: 93px;
                }

                .wpvr-occasion-anniv__image--four img {
                    max-width: 171px;
                }

                .wpvr-occasion-anniv__image--right img {
                    max-width: 111.5px;
                }

                .wpvr-occasion-anniv__lead-text h2 {
                    font-size: 22px;
                }

                .wpvr-occasion-anniv__content-area {
                    gap: 30px;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 15px;
                }

                .rex-feed-tb__notification .rex-feed-tb__cross-top {
                    width: 25px;
                    height: 25px;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 20px;
                }

                .wpvr-occasion-anniv__image--left,
                .wpvr-occasion-anniv__image--right {
                    display: none;
                }

                .wpvr-occasion-anniv__btn {
                    font-size: 12px;
                    line-height: 1;
                    font-weight: 400;
                    padding: 10px 12px;
                    margin-left: 0;
                    box-shadow: none;
                }

                .wpvr-occasion-anniv__content-area {
                    display: contents;
                    gap: 25px;
                    text-align: center;
                    align-items: center;
                }

                .wpvr-occasion-anniv__lead-text svg {
                    width: 22px;
                    margin-top: -8px;
                }


            }

            @media only screen and (max-width: 767px) {
                .wpvr-promotional-banner {
                    padding-top: 20px;
                    padding-bottom: 30px;
                    max-height: none;
                }

                .wpvr-promotional-banner {
                    max-height: none;
                }

                .wpvr-occasion-anniv__image--right,
                .wpvr-occasion-anniv__image--left {
                    display: none;
                }

                .wpvr-occasion-anniv__stroke-font {
                    font-size: 16px;
                }

                .wpvr-occasion-anniv__content-area {
                    display: contents;
                    gap: 25px;
                    text-align: center;
                    align-items: center;
                }

                .wpvr-occasion-anniv__btn-area {
                    justify-content: center;
                    padding-top: 5px;
                }

                .wpvr-occasion-anniv__btn {
                    font-size: 12px;
                    padding: 15px 24px;
                }

                .wpvr-occasion-anniv__image--group {
                    gap: 10px;
                    padding: 0;
                }
            }
        </style>
<?php

    }
}
