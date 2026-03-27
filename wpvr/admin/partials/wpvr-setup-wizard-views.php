<?php

/**
 * Setup wizard view
 *
 * @package ''
 * @since 7.4.14
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('WP VR - Setup Wizard', 'wpvr'); ?></title>
    <?php 
    do_action('admin_enqueue_scripts');
    do_action('admin_print_styles');
    do_action('admin_head');
    ?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>';
        var wpvrNonce = '<?php echo wp_create_nonce('wpvr'); ?>';
    </script>
</head>
<body>
<?php do_action('setup_wizard_before_onboarding_content'); ?>
<div id="onboarding-app" class="step-welcome">
    <!-- Welcome step content (outside container) -->
    <main class="main-content main-content-welcome" id="welcome-content">
        <section class="step active full-width" id="step-welcome">
            <div class="welcome-container">
                <div class="welcome-top">
                    <img src="<?php echo WPVR_PLUGIN_DIR_URL . 'admin/icon/wpvr-logo.svg'?>" alt="WP VR Logo" class="logo-img">
                    <span class="step-label"><?php echo esc_html__('WELCOME', 'wpvr'); ?></span>
                    <h1 class="hero-title"><?php echo esc_html__('Create your first virtual tour in under 5 minutes', 'wpvr'); ?></h1>
                    <p class="hero-sub"><?php echo esc_html__('No code. No developers. Just upload a 360° image and publish.', 'wpvr'); ?></p>
                    <ul class="welcome-checklist">
                        <li>
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 0.5C24.5604 0.5 31.5 7.43959 31.5 16C31.5 24.5604 24.5604 31.5 16 31.5C7.43959 31.5 0.5 24.5604 0.5 16C0.5 7.43959 7.43959 0.5 16 0.5Z" fill="#fff"/><path d="M16 0.5C24.5604 0.5 31.5 7.43959 31.5 16C31.5 24.5604 24.5604 31.5 16 31.5C7.43959 31.5 0.5 24.5604 0.5 16C0.5 7.43959 7.43959 0.5 16 0.5Z" stroke="#f1eff7"/><path d="M13.9 23H18.1C21.6 23 23 21.6 23 18.1V13.9C23 10.4 21.6 9 18.1 9H13.9C10.4 9 9 10.4 9 13.9V18.1C9 21.6 10.4 23 13.9 23Z" stroke="#6d667d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.9 14.6C14.6732 14.6 15.3 13.9732 15.3 13.2C15.3 12.4268 14.6732 11.8 13.9 11.8C13.1268 11.8 12.5 12.4268 12.5 13.2C12.5 13.9732 13.1268 14.6 13.9 14.6Z" stroke="#6d667d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.46893 20.865L12.9199 18.548C13.4729 18.177 14.2709 18.219 14.7679 18.646L14.9989 18.849C15.5449 19.318 16.4269 19.318 16.9729 18.849L19.8849 16.35C20.4309 15.881 21.3129 15.881 21.8589 16.35L22.9999 17.33" stroke="#6d667d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><?php echo esc_html__('Upload a 360 image', 'wpvr'); ?></span>
                        </li>
                        <li>
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 0.5C24.5604 0.5 31.5 7.43959 31.5 16C31.5 24.5604 24.5604 31.5 16 31.5C7.43959 31.5 0.5 24.5604 0.5 16C0.5 7.43959 7.43959 0.5 16 0.5Z" fill="#fff"/><path d="M16 0.5C24.5604 0.5 31.5 7.43959 31.5 16C31.5 24.5604 24.5604 31.5 16 31.5C7.43959 31.5 0.5 24.5604 0.5 16C0.5 7.43959 7.43959 0.5 16 0.5Z" stroke="#f1eff7"/><path d="M10.015 11.4775C9.0625 12.7375 8.5 14.305 8.5 16C8.5 20.14 11.86 23.5 16 23.5C20.14 23.5 23.5 20.14 23.5 16C23.5 11.86 20.14 8.5 16 8.5" stroke="#6d667d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.75 16C10.75 18.9025 13.0975 21.25 16 21.25C18.9025 21.25 21.25 18.9025 21.25 16C21.25 13.0975 18.9025 10.75 16 10.75" stroke="#6d667d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 19C17.6575 19 19 17.6575 19 16C19 14.3425 17.6575 13 16 13" stroke="#6d667d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><?php echo esc_html__('Add hotspot', 'wpvr'); ?></span>
                        </li>
                        <li>
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 0.5C24.5604 0.5 31.5 7.43959 31.5 16C31.5 24.5604 24.5604 31.5 16 31.5C7.43959 31.5 0.5 24.5604 0.5 16C0.5 7.43959 7.43959 0.5 16 0.5Z" fill="#fff"/><path d="M16 0.5C24.5604 0.5 31.5 7.43959 31.5 16C31.5 24.5604 24.5604 31.5 16 31.5C7.43959 31.5 0.5 24.5604 0.5 16C0.5 7.43959 7.43959 0.5 16 0.5Z" stroke="#f1eff7"/><path d="M22.6747 11.7905C22.2415 11.403 21.5381 11.4033 21.1044 11.7905L14.037 18.1047L10.8959 15.2985C10.4621 14.911 9.75904 14.911 9.3253 15.2985C8.89157 15.686 8.89157 16.3141 9.3253 16.7016L13.2516 20.2093C13.4683 20.4029 13.7525 20.5 14.0367 20.5C14.3209 20.5 14.6054 20.4032 14.8221 20.2093L22.6747 13.1936C23.1084 12.8064 23.1084 12.178 22.6747 11.7905Z" fill="#6d667d" stroke="#fff" stroke-width=".2"/></svg>
                            <span><?php echo esc_html__('Publish it on your site', 'wpvr'); ?></span>
                        </li>
                    </ul>
                </div>
                <div class="welcome-bottom">
                    <button class="btn-primary" id="start-tour"><?php echo esc_html__('CREATE MY FIRST TOUR', 'wpvr'); ?></button>
                    <label class="consent-label" for="consent-checkbox">
                        <input type="checkbox" id="consent-checkbox" name="consent-checkbox" checked>
                        <span><?php echo esc_html__('I agree to share non-sensitive diagnostic data to help improve WP VR.', 'wpvr'); ?></span>
                    </label>
                </div>
            </div>
        </section>
    </main>

    <!-- Steps 2-5 container with sidebar -->
    <div class="steps-container" id="steps-container">
        <!-- Exit button positioned outside container -->
        <button class="btn-skip" id="wizard-exit"><?php echo esc_html__('Exit Setup Wizard', 'wpvr'); ?></button>
        
        <aside class="sidebar" id="main-sidebar">
            <div class="logo">
                <svg width="89" height="40" viewBox="0 0 89 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M74.7954 7.66455V20.4491C77.3922 20.4491 79.4892 17.5861 79.4892 14.056C79.4892 10.5259 77.3874 7.66455 74.7954 7.66455Z" fill="#3f04fe"/><path d="M13.9254 7.58765V20.369C11.3286 20.369 9.23166 17.5059 9.23166 13.9775C9.23166 10.449 11.3319 7.58927 13.9254 7.58765Z" fill="#3f04fe"/><path d="M88.7924 30.0182C88.7924 35.5365 68.9152 39.9998 44.3962 39.9998C19.8772 39.9998 0 35.5316 0 30.0182C0 24.5048 19.8772 20.0431 44.3962 20.0431C68.9152 20.0431 88.7924 24.5048 88.7924 30.0182Z" fill="#3f04fe"/><path d="M83.3549 27.6869C83.3549 32.2021 65.9123 35.8621 44.4023 35.8621C22.8924 35.8621 5.44977 32.2021 5.44977 27.6869C5.44977 23.1716 22.894 19.5117 44.4104 19.5117C65.9269 19.5117 83.3549 23.1716 83.3549 27.6869Z" fill="#fff"/><path d="M67.2059 30.8959H21.5989C17.9309 30.8959 14.9445 27.7878 14.9445 23.9688V6.92706C14.9445 3.10809 17.9293 0 21.5989 0H67.2059C70.8739 0 73.8603 3.10809 73.8603 6.92706V23.9688C73.8603 27.7878 70.8772 30.8959 67.2059 30.8959Z" fill="#3f04fe"/><path d="M67.2335 1.06665H21.6266C18.5185 1.06665 15.9995 3.69108 15.9995 6.92739V23.9691C15.9995 27.2071 18.5185 29.8299 21.6266 29.8299H22.0859V29.812C28.8068 28.7084 36.3945 28.0867 44.4284 28.0867C52.4624 28.0867 60.0533 28.7084 66.7742 29.812V29.8299H67.2319C70.34 29.8299 72.8606 27.2071 72.8606 23.9691V6.92739C72.8622 3.69108 70.3416 1.06665 67.2335 1.06665Z" fill="#fff"/><path d="M22.4821 10.4197H24.6148L26.3628 18.4439L28.3818 10.4197H30.6005L32.5043 18.4001L34.2685 10.4197H36.4142L33.6421 20.7535H31.2968L29.4336 13.3898L27.4859 20.747L25.1536 20.7616L22.4821 10.4197Z" fill="#3f04fe"/><path d="M41.361 16.7852H39.699V20.7535H37.7076V10.4197H41.361C43.7501 10.4197 44.9446 11.8268 44.9446 13.617C44.9446 15.1865 43.9205 16.7852 41.361 16.7852ZM41.2766 15.1135C42.3997 15.1135 42.8996 14.5357 42.8996 13.617C42.8996 12.6708 42.4014 12.1076 41.2766 12.1076H39.699V15.1135H41.2766Z" fill="#3f04fe"/><path d="M56.5312 10.4197H58.6411L55.0023 20.7535H52.5986L48.9533 10.4197H51.086L53.8159 18.637L56.5312 10.4197Z" fill="#3f04fe"/><path d="M63.5688 10.4197C65.9563 10.4197 67.1508 11.8561 67.1508 13.5878C67.1508 14.8473 66.4838 16.1197 64.89 16.5644L67.2645 20.7535H64.9614L62.7719 16.7267H61.8338V20.7535H59.844V10.4197H63.5688ZM63.4974 12.1368H61.8338V15.1751H63.4974C64.6059 15.1751 65.1042 14.5681 65.1042 13.6365C65.1042 12.7049 64.6092 12.1368 63.4974 12.1368Z" fill="#3f04fe"/></svg>
            </div>
            <nav class="nav-steps">
                <div class="nav-item" data-step="step-welcome" data-number="1"><span class="nav-text">Welcome</span></div>
                <div class="nav-item" data-step="step-vertical" data-number="2"><span class="nav-text">Industry</span></div>
                <div class="nav-item" data-step="step-template" data-number="3"><span class="nav-text">Template</span></div>
                <div class="nav-item" data-step="step-preview" data-number="4"><span class="nav-text">Preview</span></div>
                <div class="nav-item" data-step="step-success" data-number="5"><span class="nav-text">Publish</span></div>
            </nav>
        </aside>

        <main class="main-content main-content-steps">
        <section class="step" id="step-vertical">
            <div class="step-indicator centered"><?php echo esc_html(sprintf(__('STEP %d OF %d', 'wpvr'), 2, 5)); ?></div>
            <h2 class="section-title"><?php echo esc_html__('What are you creating a virtual tour for?', 'wpvr'); ?></h2>
            <p class="section-sub"><?php echo esc_html__('We\'ll set things up with the right template for you.', 'wpvr'); ?></p>

            <div class="vertical-selection-grid">
                <div class="v-card" data-vertical="real-estate">
                    <div class="v-icon">🏠</div>
                    <h3><?php echo esc_html__('Real Estate', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Show property without a visit', 'wpvr'); ?></p>
                </div>
                <div class="v-card" data-vertical="hotel">
                    <div class="v-icon">🏨</div>
                    <h3><?php echo esc_html__('Hotels & Resorts', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Experience before booking', 'wpvr'); ?></p>
                </div>
                <div class="v-card" data-vertical="school">
                    <div class="v-icon">🏫</div>
                    <h3><?php echo esc_html__('Educational Institutions', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Campus tours & admissions', 'wpvr'); ?></p>
                </div>
                <div class="v-card" data-vertical="ecommerce">
                    <div class="v-icon">🛍</div>
                    <h3><?php echo esc_html__('E-commerce', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Virtual shopping experience', 'wpvr'); ?></p>
                </div>
                <div class="v-card" data-vertical="exhibitions">
                    <div class="v-icon">🎨</div>
                    <h3><?php echo esc_html__('Exhibitions', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Art & gallery showcases', 'wpvr'); ?></p>
                </div>
                <div class="v-card" data-vertical="offices">
                    <div class="v-icon">🏢</div>
                    <h3><?php echo esc_html__('Offices', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Virtual office tours', 'wpvr'); ?></p>
                </div>
                <div class="v-card" data-vertical="showrooms">
                    <div class="v-icon">🏬</div>
                    <h3><?php echo esc_html__('Showrooms', 'wpvr'); ?></h3>
                    <p><?php echo esc_html__('Virtual showroom experiences', 'wpvr'); ?></p>
                </div>
            </div>

            <footer class="onboarding-footer">
                <button class="btn-back">
                    <svg width="14" height="11" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.0347 0.75L0.75 5.0347L5.0347 9.3194" stroke="#6d667d" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.75 5.03467H0.869995" stroke="#6d667d" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?php echo esc_html__('Back', 'wpvr'); ?>
                </button>
                <div class="footer-right">
                    <button class="btn-primary-outline next-btn" data-next="step-template"><?php echo esc_html__('Continue', 'wpvr'); ?></button>
                </div>
            </footer>
        </section>

        <section class="step" id="step-template">
            <div class="step-indicator centered"><?php echo esc_html(sprintf(__('STEP %d OF %d', 'wpvr'), 3, 5)); ?></div>
            <h2 class="section-title dynamic-title"><?php echo esc_html__('Show your property without another site visit', 'wpvr'); ?></h2>
            <p class="section-sub dynamic-sub"><?php echo esc_html__('Most agents publish their first property tour in minutes.', 'wpvr'); ?></p>

            <div class="template-container">
                <div class="template-preview-box" id="template-preview-box" style="display: none;">
                    <div class="template-preview-content">
                        <div class="icon-placeholder" id="template-icon-placeholder">🏠</div>
                        <div class="template-preview-info">
                            <h3 class="template-preview-title"><?php echo esc_html__('Professional Template', 'wpvr'); ?></h3>
                            <p class="template-preview-desc"><?php echo esc_html__('Pre-configured with industry best practices and optimized settings', 'wpvr'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="upload-section" id="upload-section">
                    <div class="drop-zone" id="drop-zone">
                        <input type="file" id="image-upload" accept="image/*" style="display: none;">
                        <div class="drop-zone-content">
                            <div class="drop-zone-icon">
                                <svg width="86" height="86" viewBox="0 0 86 86" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x=".5" y=".5" width="85" height="85" rx="14.5" fill="#fff" stroke="#201cfe"/><path d="M40.6209 33.5355L40.6506 52.5015H44.5268L44.4971 33.5587L48.0683 37.1261L50.8087 34.3843L45.7452 29.3247C45.3253 28.9047 44.8267 28.5716 44.278 28.3443C43.7294 28.117 43.1413 28 42.5474 28C41.9535 28 41.3654 28.117 40.8167 28.3443C40.268 28.5716 39.7695 28.9047 39.3496 29.3247L34.286 34.3882L37.0264 37.1261L40.6209 33.5355Z" fill="#201cfe"/><path d="M54.133 48.6235V55.0838H30.8761V48.6235H27V55.0838C27 56.1118 27.4084 57.0977 28.1353 57.8246C28.8622 58.5515 29.8481 58.9599 30.8761 58.9599H54.133C55.1611 58.9599 56.147 58.5515 56.8739 57.8246C57.6008 57.0977 58.0092 56.1118 58.0092 55.0838V48.6235H54.133Z" fill="#201cfe"/></svg>
                            </div>
                            <label class="upload-section-label" for="image-upload">
                                <?php echo esc_html__('Upload my own 360 photo', 'wpvr'); ?>
                            </label>
                            <p class="drop-zone-text"><?php echo sprintf(
                                esc_html__('Drag & drop your 360° image or %s', 'wpvr'),
                                '<span class="drop-zone-link">' . esc_html__('click to browse', 'wpvr') . '</span>'
                            ); ?></p>
                            <p class="drop-zone-hint"><?php echo esc_html__('Supported files: JPEG, PNG, WebP', 'wpvr'); ?></p>
                        </div>
                    </div>
                    <div class="upload-preview" id="upload-preview" style="display: none;">
                        <div class="upload-preview-content">
                            <img id="uploaded-image-preview" alt="<?php echo esc_attr__('Uploaded 360 image', 'wpvr'); ?>">
                            <div class="upload-preview-info">
                                <p class="upload-preview-name" id="upload-preview-name"></p>
                                <button class="btn-remove-upload" id="btn-remove-upload"><?php echo esc_html__('Remove', 'wpvr'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="template-actions">
                    <button class="btn-primary-outline next-btn" data-next="step-preview" id="use-template-btn"><?php echo esc_html__('USE PROPERTY TEMPLATE', 'wpvr'); ?></button>
                </div>
            </div>

            <footer class="onboarding-footer">
                <button class="btn-back">
                    <svg width="14" height="11" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.0347 0.75L0.75 5.0347L5.0347 9.3194" stroke="#6d667d" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.75 5.03467H0.869995" stroke="#6d667d" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?php echo esc_html__('Back', 'wpvr'); ?>
                </button>
                <div class="footer-right">
                    <button class="btn-primary-outline next-btn" data-next="step-preview"><?php echo esc_html__('Continue', 'wpvr'); ?></button>
                </div>
            </footer>
        </section>

        <section class="step" id="step-preview">
            <div class="step-indicator centered"><?php echo esc_html(sprintf(__('STEP %d OF %d', 'wpvr'), 4, 5)); ?></div>
            <h2 class="section-title dynamic-prev-title"><?php echo esc_html__('This is how buyers will experience your listing', 'wpvr'); ?></h2>
            <p class="section-sub"><?php echo esc_html__('Interactive, immersive, and accessible on any device.', 'wpvr'); ?></p>

            <div class="tour-preview-window">
                <div id="panorama-container" style="display: none;">
                    <div id="panorama"></div>
                    <div class="preview-controls">
                        <span>👆 <?php echo esc_html__('Drag to rotate', 'wpvr'); ?></span>
                        <span>🔍 <?php echo esc_html__('Scroll to zoom', 'wpvr'); ?></span>
                        <span>👆 <?php echo esc_html__('Click to add hotspot', 'wpvr'); ?></span>
                    </div>
                </div>
                <div class="image-placeholder" id="preview-image-placeholder">
                    <div class="preview-controls">
                        <span>👆 <?php echo esc_html__('Drag to rotate', 'wpvr'); ?></span>
                        <span>🔍 <?php echo esc_html__('Scroll to zoom', 'wpvr'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Hotspot Modal -->
            <div class="hotspot-modal" id="hotspot-modal" style="display: none;">
                <div class="hotspot-modal-content">
                    <div class="hotspot-modal-header">
                        <h3><?php echo esc_html__('Add Hotspot', 'wpvr'); ?></h3>
                        <button class="hotspot-modal-close" id="hotspot-modal-close">&times;</button>
                    </div>
                    <div class="hotspot-modal-body">
                        <label for="hotspot-text"><?php echo esc_html__('Hotspot Text', 'wpvr'); ?></label>
                        <input type="text" id="hotspot-text" placeholder="<?php echo esc_attr__('Enter information about this location...', 'wpvr'); ?>" maxlength="100">
                        <div class="hotspot-modal-actions">
                            <button class="btn-secondary" id="hotspot-cancel"><?php echo esc_html__('Cancel', 'wpvr'); ?></button>
                            <button class="btn-primary" id="hotspot-save"><?php echo esc_html__('Add Hotspot', 'wpvr'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="onboarding-footer">
                <button class="btn-back">
                    <svg width="14" height="11" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.0347 0.75L0.75 5.0347L5.0347 9.3194" stroke="#6d667d" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.75 5.03467H0.869995" stroke="#6d667d" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?php echo esc_html__('Back', 'wpvr'); ?>
                </button>
                <div class="footer-right">
                    <button class="btn-primary-outline next-btn" data-next="step-success" id="publish-btn"><?php echo esc_html__('Continue', 'wpvr'); ?></button>
                </div>
            </footer>
        </section>

        <section class="step" id="step-success">
            <div class="step-indicator centered"><?php echo esc_html(sprintf(__('STEP %d OF %d', 'wpvr'), 5, 5)); ?></div>
            <h2 class="section-title dynamic-success-title"><?php echo esc_html__('Your tour is published!', 'wpvr'); ?></h2>
            <p class="section-sub dynamic-success-sub"><?php echo esc_html__('Your virtual tour has been created and is ready to be displayed on your website.', 'wpvr'); ?></p>

            <!-- Shortcode section will be injected here via JavaScript -->
            <div class="primary-cta-container"></div>

            <!-- Edit Tour Button -->
            <div class="edit-tour-container">
                <a class="btn-edit-tour" id="edit-tour-btn"><?php echo esc_html__('Edit Your Tour', 'wpvr'); ?></a>
            </div>

            <footer class="onboarding-footer">
                <button class="btn-back" onclick="location.reload()"><?php echo esc_html__('Start Over', 'wpvr'); ?></button>
                <div class="footer-right">
                    <button class="btn-primary-outline"><?php echo esc_html__('Go to listing', 'wpvr'); ?></button>
                </div>
            </footer>
        </section>
        </main>
    </div>
</div>
<script type="text/javascript">
(function() {
    const app = document.getElementById('onboarding-app');
    const steps = document.querySelectorAll('.step');
    const stepsContainer = document.getElementById('steps-container');
    const welcomeContent = document.getElementById('welcome-content');
    
    // Function to update app class based on active step
    function updateAppClass() {
        const activeStep = document.querySelector('.step.active');
        if (activeStep && activeStep.id === 'step-welcome') {
            app.classList.add('step-welcome');
            app.classList.remove('sidebar-visible');
            if (stepsContainer) stepsContainer.style.display = 'none';
            if (welcomeContent) welcomeContent.style.display = 'flex';
        } else {
            app.classList.remove('step-welcome');
            app.classList.add('sidebar-visible');
            if (stepsContainer) stepsContainer.style.display = 'flex';
            if (welcomeContent) welcomeContent.style.display = 'none';
        }
    }
    
    // Observer to watch for step changes
    const observer = new MutationObserver(updateAppClass);
    steps.forEach(step => {
        observer.observe(step, { attributes: true, attributeFilter: ['class'] });
    });
    
    // Initial update
    updateAppClass();
})();
</script>
<?php wp_print_scripts(); ?>
<?php do_action('admin_footer'); ?>
</body>
</html>