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
<?php
/**
 * get rollback version of WPVR
 *
 * @return array|mixed
 *
 * @src Inspired from Elementor roll back options
 */
function rex_wpvr_get_roll_back_versions()
{
    $max_number = 5;
    $transient_key = 'rex_wpvr_rollback_versions_' . WPVR_VERSION;

    // Check cached version
    if ($rollback_versions = get_transient($transient_key)) {
        return $rollback_versions;
    }

    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    $plugin_information = plugins_api('plugin_information', ['slug' => 'wpvr']);

    if (empty($plugin_information->versions) || !is_array($plugin_information->versions)) {
        return [];
    }

    $latest_version = $plugin_information->versions;
    unset($latest_version['trunk']);

    $valid_versions = [];

    foreach ($latest_version as $version => $download_link) {
        $lowercase_version = strtolower($version);
        $is_valid_rollback_version =
            preg_match('/^\d+\.\d+\.\d+$/', $version) &&
            !preg_match('/(beta|rc|dev)/i', $lowercase_version) &&
            version_compare($version, WPVR_VERSION, '<');

        /**
         * Filters whether the rollback version is valid.
         *
         * @param bool   $is_valid_rollback_version Whether the rollback version is valid.
         * @param string $lowercase_version         The lowercase version string.
         */
        $is_valid_rollback_version = apply_filters(
            'rex_wpvr_is_valid_rollback_version',
            $is_valid_rollback_version,
            $lowercase_version
        );

        if ($is_valid_rollback_version) {
            $valid_versions[] = $version;
        }
    }

    usort($valid_versions, function ($a, $b) {
        return version_compare($b, $a);
    });

    $rollback_versions = array_slice($valid_versions, 0, $max_number);

    // Cache the results for a week
    set_transient($transient_key, $rollback_versions, WEEK_IN_SECONDS);

    return $rollback_versions;
}


$rollback_versions     = function_exists( 'rex_wpvr_get_roll_back_versions' ) ? rex_wpvr_get_roll_back_versions() : array();

?>
    <!-- This file should display the admin pages -->
    <div class="wpvr-global-settings">
        <ul class="tabs tabs-icon rex-tabs">
            <li class="tab col s3 wpvr_tabs_row general-settigs">
                <a href="#tab1">
                    <svg width="19" height="18" fill="none" viewBox="0 0 19 18" xmlns="http://www.w3.org/2000/svg"><path fill="#ABB4C2" stroke="#ABB4C2" stroke-width=".1" d="M17.71 10.43l-.75-.616a1.055 1.055 0 010-1.628l.75-.617c.532-.438.668-1.19.323-1.788L16.554 3.22a1.402 1.402 0 00-1.71-.614l-.909.34a1.055 1.055 0 01-1.41-.814l-.16-.957A1.402 1.402 0 0010.978 0H8.021c-.69 0-1.274.494-1.387 1.175l-.16.957a1.055 1.055 0 01-1.41.814l-.908-.34a1.402 1.402 0 00-1.711.614L.966 5.78a1.402 1.402 0 00.324 1.79l.749.616a1.055 1.055 0 010 1.628l-.749.617a1.402 1.402 0 00-.324 1.789l1.479 2.56a1.402 1.402 0 001.71.614l.91-.34a1.055 1.055 0 011.41.814l.159.957C6.747 17.505 7.33 18 8.02 18h2.957c.69 0 1.274-.494 1.387-1.175l.16-.957a1.054 1.054 0 011.41-.814l.908.34a1.402 1.402 0 001.712-.614l1.478-2.56a1.402 1.402 0 00-.324-1.79zm-2.373 3.647l-.909-.34a2.46 2.46 0 00-3.29 1.9l-.16.957H8.021l-.16-.957a2.46 2.46 0 00-3.29-1.9l-.909.34-1.478-2.56.749-.617a2.46 2.46 0 000-3.8l-.75-.616 1.48-2.561.908.34a2.46 2.46 0 003.29-1.9l.16-.957h2.957l.16.957a2.46 2.46 0 003.29 1.9l.909-.34 1.479 2.56s0 0 0 0l-.75.617a2.46 2.46 0 000 3.8l.75.616-1.48 2.561zM9.5 5.531A3.473 3.473 0 006.03 9a3.473 3.473 0 003.47 3.469A3.473 3.473 0 0012.968 9 3.473 3.473 0 009.5 5.531zm0 5.532A2.065 2.065 0 017.437 9c0-1.137.925-2.063 2.063-2.063 1.137 0 2.062.926 2.062 2.063A2.065 2.065 0 019.5 11.063z"/></svg>

                    <?php _e('General Settings', 'wpvr'); ?>
                </a>
            </li>
        </ul>

        <div class="wpvr-global-settings-tab-content">
            <div id="tab1" class="block-wrapper">
                <h3 class="tab-content-title"><?php _e('General Settings', 'wpvr'); ?></h3>
                <div class="rex-upgrade wpvr-settings <?php echo is_plugin_active('wpvr-pro/wpvr-pro.php') ? 'pro-active' : ''; ?>">
                    <h4 class="settings-box-title"><?php _e('Setup Options', 'wpvr'); ?></h4>
                    <div class="parent settings-wrapper">
                        <div class="wpvr_role-container">
                            <ul class="settings-ul">
                                <?php
                                $is_wpvr_premium = apply_filters('is_wpvr_premium', false);
                                $is_integration_module = apply_filters('is_integration_module', false);
                                $editor_active = get_option('wpvr_editor_active');
                                $author_active = get_option('wpvr_author_active');
                                $fontawesome_disable = get_option('wpvr_fontawesome_disable');
                                $cardboard_disable = get_option('wpvr_cardboard_disable');
                                $wpvr_webp_conversion = get_option('wpvr_webp_conversion');
                                $mobile_media_resize = get_option('mobile_media_resize');
                                $wpvr_script_control = get_option('wpvr_script_control');
                                $wpvr_script_list = get_option('wpvr_script_list');
                                $wpvr_video_script_control = get_option('wpvr_video_script_control');
                                $wpvr_video_script_list = get_option('wpvr_video_script_list');
                                $high_res_image = get_option('high_res_image');
                                $dis_on_hover = get_option('dis_on_hover');
                                $enable_woocommerce = get_option('wpvr_enable_woocommerce', false);
                                $dokan_vendor_active = get_option('dokan_vendor_active', false);
                                ?>
                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Allow the Editors of your site to Create, Edit, Update, and Delete virtual tours (They can access other users' tours):", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($editor_active == "true") {
                                            ?>
                                            <input id="wpvr_editor_active" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_editor_active" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_editor_active"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __('Editors will be able to Create, Edit, Update, and Delete all virtual tours.', 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Allow the Authors of your site to Create, Edit, Update, and Delete virtual tours (They can access their own tours only):", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($author_active == "true") {
                                            ?>
                                            <input id="wpvr_author_active" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_author_active" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_author_active"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __('Authors will be able to Create, Edit, Update, and Delete their own virtual tours only.', 'wpvr'); ?></p>
                                    </span>
                                </li>
                                <?php if(is_plugin_active( 'dokan-lite/dokan.php' ) || is_plugin_active( 'dokan-pro/dokan.php' )) { ?>
                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Allow Dokan Vendors of your site to Create, Edit, Update, and Delete virtual tours (They can access their own tours only):", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($dokan_vendor_active == "true") {
                                            ?>
                                            <input id="wpvr_dokan_vendor_active" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_dokan_vendor_active" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_dokan_vendor_active"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __('Dokan vendor will be able to Create, Edit, Update, and Delete their own virtual tours only.', 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <?php } ?>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Disable Fontawesome from WP VR:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($fontawesome_disable == "true") {
                                            ?>
                                            <input id="wpvr_fontawesome_disable" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_fontawesome_disable" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_fontawesome_disable"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p>
                                            <?php echo __('WP VR will not load Font Awesome library.', 'wpvr'); ?>
                                        </p>
                                    </span>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Enable mobile media resizer:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($mobile_media_resize == "true") {
                                            ?>
                                            <input id="mobile_media_resize" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="mobile_media_resize" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="mobile_media_resize"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __('WP VR will resize each scenes for mobile devices.', 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Disable WordPress Large Image Handler on WP VR:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($high_res_image == "true") {
                                            ?>
                                            <input id="high_res_image" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="high_res_image" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="high_res_image"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __("WordPress's default large image handler for images larger than 2560px will be disabled for WP VR. So can create virtual tours with extremely high-quality images. Enabling it will also show high res image on mobile devices. Many devices may not support that resolution.", 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Disable On Hover Content for Mobile:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($dis_on_hover == "true") {
                                            ?>
                                            <input id="dis_on_hover" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="dis_on_hover" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="dis_on_hover"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __("You can disable on hover content for mobile devices. As most of the devices are touch based.", 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Enable script control (It will load the WP VR scripts on the pages with virtual tours only):", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($wpvr_script_control == "true") {
                                            ?>
                                            <input id="wpvr_script_control" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_script_control" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_script_control"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __("WP VR assets will be loaded on your allowed pages only. If you turn this on, you have to list the URL's of the pages with virtual tours on the 'List of allowed pages to load WP VR scripts' option", 'wpvr'); ?></p>
                                    </span>

                                    <div class="inner-settings-wrapper enqueue-script wpvr_enqueue_script_list">
                                        <p class="single-settings-inner-title">
                                            <?php echo __('List of allowed pages to load WP VR scripts (The URLs of the pages on your site with virtual tours):', 'wpvr'); ?> 
                                        </p>

                                        <div class="settings-input-wrapper">
                                            <input type="text" id="wpvr_script_list" class="materialize-textarea" placeholder="https://example.com/tour1/,https://example.com/tour2/" value="<?php echo $wpvr_script_list; ?>" >
                                            <span class="hints"><?php echo __("List the pages with virtual tours like this: https://example.com/tour1/, https://example.com/tour2/", 'wpvr'); ?></span>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Enable Video JS control (It will load the WP VR Video JS library in the listed pages only):", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($wpvr_video_script_control == "true") {
                                            ?>
                                            <input id="wpvr_video_script_control" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_video_script_control" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_video_script_control"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __("WP VR assets will be loaded on your allowed pages only. If you turn this on, you have to list the URL's of the pages with virtual tours on the 'List of allowed pages to load WP VR scripts' option", 'wpvr'); ?></p>
                                    </span>

                                    <div class="inner-settings-wrapper enqueue-video-script enqueue-script wpvr_enqueue_video_script_list">
                                        <p class="single-settings-inner-title">
                                            <?php echo __('List of allowed pages to load WP VR Video JS library (The URLs of the pages on your site, You want to load Video JS):', 'wpvr'); ?> 
                                        </p>

                                        <div class="settings-input-wrapper">
                                            <input type="text" id="wpvr_video_script_list" class="materialize-textarea" placeholder="https://example.com/video-tour1/,https://example.com/video-tour2/" value="<?php echo $wpvr_video_script_list; ?>">
                                            <span class="hints"><?php echo __("List the pages like this: https://example.com/tour1/, https://example.com/tour2/", 'wpvr'); ?></span>
                                        </div>
                                    </div>
                                </li>

                                <!-- WPVR front-end notice -->
                                <li class="enqueue-script front-notice">
                                    <?php
                                    $wpvr_frontend_notice = false;
                                    $wpvr_frontend_notice_area = '';
                                    $wpvr_frontend_notice = get_option('wpvr_frontend_notice');
                                    $wpvr_frontend_notice_area = get_option('wpvr_frontend_notice_area');
                                    if (!$wpvr_frontend_notice_area) {
                                        $wpvr_frontend_notice_area = __("Flip the phone to landscape mode for a better experience of the tour.", "wpvr");
                                    }
                                    ?>
                                    <p class="single-settings-title">
                                        <?php echo __("Front-End Notice for Mobile Visitors:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if ($wpvr_frontend_notice == "true") {
                                            ?>
                                            <input id="wpvr_frontend_notice" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_frontend_notice" type="checkbox">
                                            <?php
                                        }
                                        ?>
                                        <label for="wpvr_frontend_notice"></label>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __("The notice will appear on the front end of the virtual tour if viewed from a mobile device.", 'wpvr'); ?></p>
                                    </span>

                                    <input type="text" id="wpvr_frontend_notice_area" class="materialize-textarea" placeholder="Add your notice here" value="<?php echo $wpvr_frontend_notice_area; ?>">
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("VR GLass Support:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <?php
                                        if(is_plugin_active('wpvr-pro/wpvr-pro.php')){
                                            if ($cardboard_disable == 'true') {
                                                ?>
                                                <input id="wpvr_cardboard_disable" type="checkbox" checked>
                                                <?php
                                            } else {
                                                ?>
                                                <input id="wpvr_cardboard_disable" type="checkbox" >
                                                <?php
                                            }
                                        }
                                        ?>
                                        <?php if(is_plugin_active('wpvr-pro/wpvr-pro.php')){ ?>
                                            <label for="wpvr_cardboard_disable"></label>
                                        <?php }else{ ?>

                                            <div class='wpvr_cardboard_disable_is_pro'>
                                                <span>Pro</span>
                                                <label for="wpvr_cardboard_disable" class='wpvr_cardboard_disable_label'></label>
                                            </div>
                                        <?php } ?>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __('Will activate the VR Glass support on mobile devices. This is the Beta release. So, if you face any issues with it, please contact us at support@rextheme.com', 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <li>
                                    <p class="single-settings-title">
                                        <?php echo __("Convert any jpeg or png format image to WebP on media upload:", "wpvr"); ?>
                                    </p>

                                    <span class="wpvr-switcher">
                                        <!-- WPVR front-end notice -->
                                        <?php if (is_plugin_active('wpvr-pro/wpvr-pro.php')) { ?>

                                        <?php
                                        if ($wpvr_webp_conversion == 'true') {
                                            ?>
                                            <input id="wpvr_webp_conversion" type="checkbox" checked>
                                            <?php
                                        } else {
                                            ?>
                                            <input id="wpvr_webp_conversion" type="checkbox" >
                                            <?php
                                        }
                                        ?>
                                        <?php } ?>

                                        <?php if(is_plugin_active('wpvr-pro/wpvr-pro.php')){ ?>
                                            <label for="wpvr_webp_conversion"></label>
                                        <?php }else{ ?>

                                            <div class='wpvr_cardboard_disable_is_pro'>
                                                <span>Pro</span>
                                                <label for="wpvr_cardboard_disable" class='wpvr_cardboard_disable_label'></label>
                                            </div>
                                        <?php } ?>
                                    </span>

                                    <span class="wpvr-tooltip">
                                        <span class="icon">
                                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg"><path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333" d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z"/></svg>
                                        </span>
                                        <p><?php echo __('Will convert any jpeg or png image to webp format during media upload. will decrease the image size with no quality compromise and help the site to load faster.', 'wpvr'); ?></p>
                                    </span>
                                </li>

                                <li>
                                    <form class="wpvr-version" id="trigger-rollback">
                                        <?php wp_nonce_field( 'wpvr_rollback','wpvr_rollback' ); ?>
                                        <p class="single-settings-title">
                                            <?php _e('Select a Version to Rollback', 'wpvr'); ?>
                                        </p>
                                        <select class="wpvr-version-select" name="wpvr_version">
                                            <?php
                                            foreach ( $rollback_versions as $version ) {
                                                echo "<option value='".esc_attr( $version )."'>".esc_html($version)."</option>";
                                            }
                                            ?>
                                        </select>

                                        <input class="wpvr-btn" type="submit" value="Rollback">
                                    </form>
                                </li>
                            </ul>

                        </div>

                    </div>
                </div>

                <div class="settings-footer-area">
                    <span class="wpvr-alert box wpvr-success">
                        <?php echo __('Succesfully Saved', 'wpvr'); ?>
                    </span>

                    <button class="wpvr-btn reset" type="button" id="wpvr_role_reset">
                        <?php echo __('Reset Defaults', 'wpvr'); ?>
                    </button>

                    <button class="wpvr-btn" type="submit" id="wpvr_role_submit">
                        <?php echo __('Save Settings', 'wpvr'); ?>
                        <span class="wpvr-loader"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php

if(is_plugin_active('divi-builder/divi-builder.php')){
    ?>
    <script>
        (function ($) {
            $(".wpvr-global-settings .block-wrapper:not(#tab1)").hide()
            $('.wpvr-global-settings li.tab a').first().addClass("active");
            $('.wpvr-global-settings li.tab').on('click', function(){
                var target_id = $(this).find("a").attr('href');
                $(".wpvr-global-settings li.tab a").removeClass('active');
                $(this).find("a").addClass('active');
                $(target_id).show();
                $(target_id).siblings('.block-wrapper').hide();
            })
        })(jQuery);
    </script>
    <?php
}

?>