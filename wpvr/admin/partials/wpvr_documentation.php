<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Wpvr
 * @subpackage Wpvr/admin/partials
 */
?>

<!-- This file should display the admin pages -->
<div class="rex-onboarding">
    <ul class="tabs tabs-icon rex-tabs">
        <li class="tab col s3 wpvr_tabs_row">
            <a href="#">
                <svg height="16px" width="17px" viewBox="0 -10 511.987 511" xmlns="http://www.w3.org/2000/svg"><path d="M114.594 491.14c-5.61 0-11.18-1.75-15.934-5.187a27.223 27.223 0 01-10.582-28.094l32.938-145.09L9.312 214.81a27.188 27.188 0 01-7.976-28.907 27.208 27.208 0 0123.402-18.71l147.797-13.419L230.97 17.027C235.277 6.98 245.089.492 255.992.492s20.715 6.488 25.024 16.512l58.433 136.77 147.774 13.417c10.882.98 20.054 8.344 23.425 18.711 3.372 10.368.254 21.739-7.957 28.907L390.988 312.75l32.938 145.086c2.414 10.668-1.727 21.7-10.578 28.098-8.832 6.398-20.61 6.89-29.91 1.3l-127.446-76.16-127.445 76.203c-4.309 2.559-9.11 3.864-13.953 3.864zm141.398-112.874c4.844 0 9.64 1.3 13.953 3.859l120.278 71.938-31.086-136.942a27.21 27.21 0 018.62-26.516l105.473-92.5-139.543-12.671a27.18 27.18 0 01-22.613-16.493L255.992 39.895 200.844 168.96c-3.883 9.195-12.524 15.512-22.547 16.43L38.734 198.062l105.47 92.5c7.554 6.614 10.858 16.77 8.62 26.54l-31.062 136.937 120.277-71.914c4.309-2.559 9.11-3.86 13.953-3.86zm-84.586-221.848s0 .023-.023.043zm169.13-.063l.023.043c0-.023 0-.023-.024-.043zm0 0"/></svg>
                <?php _e('Free vs Pro', 'wpvr'); ?>
            </a>
        </li>
    </ul>

    <div class="block-wrapper">
        <div class="wpvr-compare">
            <?php
                $pro_url = add_query_arg('wpvr-dashboard', '1', 'https://rextheme.com/wpvr/wpvr-pricing/');
                
                $features = [
                    __('Unlimited Scenes (Up to 5 in Free)', 'wpvr'),
                    __('Unlimited Hotspots (Up to to 5 for a scene in free)', 'wpvr'),
                    __('Publish Tours Anywhere (Embed Add-on)', 'wpvr'),
                    __('WooCommerce Add-on', 'wpvr'),
                    __('Gyroscope Support', 'wpvr'),
                    __('Panorama Scene Gallery', 'wpvr'),
                    __('Explainer Video', 'wpvr'),
                    __('Tour Background Audio', 'wpvr'),
                    __('Info-type Hotspots (Heading, Image, Text, Video, Gif, Links)', 'wpvr'),
                    __('Scene-type Hotspots (Connect Panoramas)', 'wpvr'),
                    __('Custom Icon & Color for Hotspots (Using CSS)', 'wpvr'),
                    __('900+ Icons & RGB Color Support for Hotspots', 'wpvr'),
                    __('Partial Panorama / Mobile Panorama Support', 'wpvr'),
                    __('360 Video Support', 'wpvr'),
                    __('Google Street View', 'wpvr'),
                    __('Cubemap Image Support', 'wpvr'),
                    __('VR Glass Support for Video Tours', 'wpvr'),
                    __('Fluent Forms Add-on', 'wpvr'),
                    __('Autoload Tours', 'wpvr'),
                    __('Custom Rotation Settings', 'wpvr'),
                    __('Full Page Virtual Tours', 'wpvr'),
                    __('Custom Preview Image & Text', 'wpvr'),
                    __('Company Logo & Description', 'wpvr'),
                    __('Import & Export Virtual Tours', 'wpvr'),
                    __('Duplicate Tours with One Click', 'wpvr'),
                    __('Control Horizontal & Vertical View of Panorama Images', 'wpvr'),
                    __('Custom Zoom Settings', 'wpvr'),
                    __('Custom Panorama Loading Face', 'wpvr'),
                    __('Background Panoramas', 'wpvr'),
                    __('Home Button to visit Default Scene', 'wpvr'),
                    __('Scene Title', 'wpvr'),
                    __('Scene Author with URL', 'wpvr'),
                    __('Enable or Disable Keyboard Movement Control.', 'wpvr'),
                    __('Enable or Disable Keyboard Zoom Control.', 'wpvr'),
                    __('Enable or Disable Mouse Drag Control.', 'wpvr'),
                    __('Enable or Disable Mouse Zoom control.', 'wpvr'),
                    __('Mouse Control.', 'wpvr'),
                    __('On Screen Compass.', 'wpvr'),
                    __('Scene Titles on Gallery.', 'wpvr'),
                    __('Customize Icon & Logo of Control Buttons.', 'wpvr'),
                    __('Autoload & Autoplay Video Tours.', 'wpvr')
                ];

                // Define the corresponding icons for free and pro versions
                $free_icons = array_fill(0, 41, 'cross');

                $free_icons[8] = 'check';
                $free_icons[9] = 'check';
                $free_icons[10] = 'check';
                $free_icons[13] = 'check';
                $free_icons[16] = 'check';
                $free_icons[18] = 'check';
                $free_icons[20] = 'check';

                $pro_icons = array_fill(0, 41, 'check');

                echo '<div class="compare-tbl-wrapper">';
                    echo '<div class="comparison-tbl-wrapper-area">';

                        echo '<ul class="single-feature list-header">';
                        echo '<li class="comparison-tbl-col feature">' . __('features', 'wpvr') . '</li>';
                        echo '<li class="comparison-tbl-col free">' . __('free', 'wpvr') . '</li>';
                        echo '<li class="comparison-tbl-col pro">' . __('pro', 'wpvr') . '</li>';
                        echo '</ul>';

                        echo '<div class="comparison-tbl-body">';

                            foreach ($features as $index => $feature) {
                                echo '<ul class="single-feature feature-list">';

                                    echo '<li class="comparison-tbl-col feature"><p>' . $feature . '</p></li>';
                                    echo '<li class="comparison-tbl-col free"><span class="icon no"><img loading="lazy" src="' . WPVR_PLUGIN_DIR_URL . 'admin/icon/' . $free_icons[$index] . '.svg" alt="' . $free_icons[$index] . '"></span></li>';
                                    echo '<li class="comparison-tbl-col pro"><span class="icon yes"><img loading="lazy" src="' . WPVR_PLUGIN_DIR_URL . 'admin/icon/' . $pro_icons[$index] . '.svg" alt="' . $pro_icons[$index] . '"></span></li>';

                                echo '</ul>';
                            }
                        echo '</div>';

                    echo '</div>';

                    echo '<div class="comparison-tbl-footer">';
                        echo '<div class="footer-btn comparison-tbl-col">';
                            echo '<a class="wpvr-btn get-pro" href="' . esc_url($pro_url) . '" title="' . esc_attr__('Upgrade to Pro', 'wpvr') . '" target="_blank">';
                                echo esc_html__('Upgrade to Pro', 'wpvr');
                            echo '</a>';
                        echo '</div>';
                    echo '</div>';
                

                echo '</div>';

            ?>
        </div>
    </div>
</div>

<?php

 if(is_plugin_active('divi-builder/divi-builder.php')){
     ?>
     <script>
         (function ($) {
        //  $(".rex-onboarding .block-wrapper:not(#tab1)").hide()
         $('.rex-onboarding li.tab a').first().addClass("active");
         $('.rex-onboarding li.tab').on('click', function(){
             var target_id = $(this).find("a").attr('href');
             $(".rex-onboarding li.tab a").removeClass('active');
             $(this).find("a").addClass('active');
             $(target_id).show();
             $(target_id).siblings('.block-wrapper').hide();
         })
         })(jQuery);
     </script>
     <?php
 }

?>