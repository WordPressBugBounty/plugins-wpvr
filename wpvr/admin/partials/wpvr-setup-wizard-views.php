<?php

/**
 * Setup wizard view
 *
 * @package ''
 * @since 7.4.14
 */
?>

<!DOCTYPE html>
<html style="background-color: #F2EFFF;" lang="en" xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e('WPVR - Setup Wizard', 'wpvr'); ?></title>
    <?php do_action('admin_enqueue_scripts'); ?>
    <?php do_action('admin_print_styles'); ?>
    <?php do_action('admin_head'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript">
        addLoadEvent = function(func) {
            if (typeof jQuery != "undefined") jQuery(document).ready(func);
            else if (typeof wpOnload != 'function') {
                wpOnload = func;
            } else {
                var oldonload = wpOnload;
                wpOnload = function() {
                    oldonload();
                    func();
                }
            }
        };
        var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>';
        var pagenow = '';
    </script>
</head>

<body>
    <div class="setup-wizard__container">
        <div class="setup-wizard__inner-container">
            <div id="wizardContainer" style="height:100vh;">

            </div>
        </div>
    </div>
    <?php
    wp_enqueue_media(); // add media
    wp_print_scripts(); // window.wp
    do_action('admin_footer');

    $current_date = date('Y-m-d H:i:s');
    $start_date = '2026-01-27 00:00:00';
    $end_date = '2026-02-05 23:59:59';
    $discount_percentage = '';
    $discount_price = '';
    $price = '$99.99';
    if ($current_date >= $start_date && $current_date <= $end_date) {
        $discount_percentage = "Save 25%";
        $discount_price = "$74.99";
    }  
    else {
        //$discount_percentage = "Save 15%";
        $discount_price = "$74.99";
    }

    $data = array(
        'stepOne' => array(
            'step_text' => __("Welcome", "wpvr"),
            'welcome_section_heading' => __("Hello, welcome to", "wpvr"),
            'welcome_section_strong_heading' => array(
                __("WPVR", "wpvr"),
            ),
            'welcome_section_description' => __("WP VR is a powerful virtual tour creator for WordPress that
                        lets you create captivating walkthroughs of any property or
                        location, with detailed and interactive information to enrich
                        your audience's experience with your business.", "wpvr"),
            // 'img_alt' => __("Welcome banner image", "wpvr"),
            'welcome_section_button_text' => array(
                __("Let’s create your first tour", "wpvr"),
                __("Check the guide", "wpvr"),
            ),
            'footer_section_button_text' => array(
                __("Let’s create your first tour", "wpvr"),
                __("Next", "wpvr"),
            ),

            // feature section data
            'feature_section_heading' => __("", "wpvr"),
            'feature_section_strong_heading' => array(
                __("WPVR Features", "wpvr"),
            ),
            'feature_section_description' => __("Transform Properties, Rooms & Spaces into 360 Virtual Tours
                Using WP VR Features.", "wpvr"),
            'feature_icon_one' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/video.svg',
            'feature_icon_two' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/brand.svg',
            'feature_icon_three' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/lead-form.svg',
            'feature_icon_four' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/password.svg',
            'feature_icon_five' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/sharing.svg',
            'feature_section_button_text' => array(
                __("Check All Features", "wpvr"),
                __("", "wpvr"),
            ),

            'feature_explainer_video' => array(
                __("Guided Explainer Video", "wpvr"),
                __("Display Your Entire Property With Virtual Floor Plans.", "wpvr"),
            ),
            'feature_brand_identity' => array(
                __("Reinforce Brand Identity", "wpvr"),
                __("Reinforce Your Brand Identity within Virtual Tours.", "wpvr"),
            ),
            'feature_lead_forms' => array(
                __("Integrated Leads Forms", "wpvr"),
                __("Capture Valuable Leads with Integrated Forms.", "wpvr"),
            ),
            'feature_password' => array(
                __("Password Protected Tour", "wpvr"),
                __("Secure Your Virtual Tours with Password Protection.", "wpvr"),
            ),
            'feature_sharing' => array(
                __("Easy Sharing Tour", "wpvr"),
                __("Maximize Exposure By Enabling Easy Sharing Of Your Virtual Tours.", "wpvr"),
            ),

            // pro features data
            'pro_feature_section_heading' => __("WPVR", "wpvr"),
            'pro_feature_section_strong_heading' => array(
                __("Pro Features", "wpvr"),
            ),
            'pro_feature_icon_one' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/gallery.svg',
            'pro_feature_title_one' => __("Unlimited Scenes", "wpvr"),

            'pro_feature_icon_two' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/panorama-control.svg',
            'pro_feature_title_two' => __("Custom Panorama Controls", "wpvr"),

            'pro_feature_icon_three' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/custom-cta.svg',
            'pro_feature_title_three' => __("Custom CTA Button", "wpvr"),

            'pro_feature_icon_four' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/floorplan.svg',
            'pro_feature_title_four' => __("Complete Floor Plan", "wpvr"),

            'pro_feature_icon_five' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/unlimited-hotspot.svg',
            'pro_feature_title_five' => __("Unlimited Hotspots", "wpvr"),

            'pro_feature_icon_six' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/embed-addon.svg',
            'pro_feature_title_six' => __("Embed Addon", "wpvr"),

            'pro_feature_icon_seven' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/partial-panoroma.svg',
            'pro_feature_title_seven' => __("Partial Panorama", "wpvr"),

            'pro_feature_icon_eight' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/cube.svg',
            'pro_feature_title_eight' => __("Cubemap Image Support", "wpvr"),

            'pro_feature_button_text' => __("Upgrade To Pro Now", "wpvr"),
        ),

        'stepTwo' => array(
            'step_text' => __("Settings & Industry", "wpvr"),

            // industry section data
            'industry_section_heading' => __("Select Your", "wpvr"),
            'industry_section_strong_heading' => array(
                __("Industry", "wpvr"),
            ),

            // general settings section data
            'general_section_heading' => __("", "wpvr"),
            'general_section_strong_heading' => array(
                __("General Settings", "wpvr"),
            ),

            'footer_section_button_text' => array(
                __("Let’s create your first tour", "wpvr"),
                __("Next", "wpvr"),
            ),
        ),

        'stepThree' => array(
            'step_text' => __("Done", "wpvr"),
            'heading' => __("Get", "wpvr"),
            'strong_heading' => array(
                __("Email Notification", "wpvr"),
            ),

            'done_icon' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/done-icon.svg',
            'quote_icon' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/quote-icon.svg',

            'footer_section_button_text' => array(
                __("Let’s create your first tour", "wpvr"),
                __("Upgrade To Pro", "wpvr"),
            ),

            'testimonial_one' => array(
                __("The WP VR 360 Panorama and Virtual Tour Builder for WordPress is a game-changer for virtual reality enthusiasts. This plugin offers an exceptional VR experience, allowing you to create captivating 360-degree panoramas and immersive virtual tours on your WordPress website. The support team is top-notch, ensuring you have all the assistance you need.", "wpvr"),
                __("-- Shweta Bathani", "wpvr"),
            ),
            'testimonial_two' => array(
                __("These guys are real pro's! Any problem, and they will stick with it until it is fixed. Amazing support. I just love the plugin, and I just love where it is going, and all the great idea's they will be implementing. Can't wait! But I am already thrilled with what is there now, too. Keep up the good work, guys!", "wpvr"),
                __("-- Firat Sabah", "wpvr"),
            ),
        ),
    );

    $setup_wizard_price = array(
            'discount_price'        => $discount_price,
            'discount_percentage_text' => $discount_percentage,
            'original_price'        => $price
    );

    error_log(print_r($setup_wizard_price, true));

    $popular_industries = array(
        'real_estate'   => array(
            'name' => 'Real Estate',
            'logo_url' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/real-estate.php',
        ),
        'hotel'   => array(
            'name' => 'Hotel',
            'logo_url' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/hotel.svg',
        ),
        'art_gallery'   => array(
            'name' => 'Art Gallery',
            'logo_url' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/art-gallary.svg',
        ),
        'beauty_parlor'   => array(
            'name' => 'Beauty Parlor',
            'logo_url' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/beauty-parlor.svg',
        ),
        'car_showroom'   => array(
            'name' => 'Car Showroom',
            'logo_url' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/car-showroom.svg',
        ),
        'pinterest'   => array(
            'name' => 'Pub/Bar',
            'feed_url' => 'post-new.php?post_type=product-feed&rex_feed_merchant=pinterest',
            'logo_url' => WPVR_ASSET_PATH . 'icon/setup-wizard-images/pub.svg',
        )
    );
    ?>

    <script type="text/javascript">
        const rex_wpvr_wizard_translate_string = <?php echo wp_json_encode($data); ?>;
        const logoUrl = <?php echo json_encode(esc_url(WPVR_ASSET_PATH . 'icon/setup-wizard-images/wpvr-logo.webp')); ?>;
        const bannerUrl = <?php echo json_encode(esc_url(WPVR_ASSET_PATH . 'icon/setup-wizard-images/welcome-image.webp')); ?>;
        const thumnailImage = <?php echo json_encode(esc_url(WPVR_ASSET_PATH . 'icon/setup-wizard-images/youtube-thumbnill.webp')); ?>;
        const woocommerceUrl = <?php echo json_encode(esc_url(WPVR_ASSET_PATH . 'icon/setup-wizard-images/woocommerce-logo.webp')); ?>;
        // const yt_video = 'https://www.youtube.com/embed/SWsv-bplne8?&autoplay=1"';
        const setup_wizard_admin_url = <?php echo json_encode(esc_url(get_admin_url())); ?>;

        const popular_industries = <?php echo json_encode($popular_industries); ?>;
        const discount_information = <?php echo wp_json_encode($setup_wizard_price)?>;
        const wpvr_opt_in_toggle = <?php echo json_encode(get_option('wpvr_opt_in_toggle', '1')); ?>;
    </script>
    <script src="<?php echo WPVR_JS_PATH . 'setup-wizard/setup_wizard.js' ?>">
    </script>
</body>

</html>