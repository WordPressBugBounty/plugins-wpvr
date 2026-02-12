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
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.5 12.5V15.8333C17.5 16.2754 17.3244 16.6993 17.0118 17.0118C16.6993 17.3244 16.2754 17.5 15.8333 17.5H4.16667C3.72464 17.5 3.30072 17.3244 2.98816 17.0118C2.67559 16.6993 2.5 16.2754 2.5 15.8333V12.5" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14.1667 6.66667L10 2.5L5.83334 6.66667" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 2.5V12.5" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span><?php echo esc_html__('Upload a 360 image', 'wpvr'); ?></span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 17.5C14.1421 17.5 17.5 14.1421 17.5 10C17.5 5.85786 14.1421 2.5 10 2.5C5.85786 2.5 2.5 5.85786 2.5 10C2.5 14.1421 5.85786 17.5 10 17.5Z" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 6.66667V10" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 13.3333H10.0083" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span><?php echo esc_html__('Add hotspot', 'wpvr'); ?></span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.6667 3.33333H3.33334C2.41286 3.33333 1.66667 4.07952 1.66667 5V15C1.66667 15.9205 2.41286 16.6667 3.33334 16.6667H16.6667C17.5872 16.6667 18.3333 15.9205 18.3333 15V5C18.3333 4.07952 17.5872 3.33333 16.6667 3.33333Z" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.66667 10L8.33334 11.6667L13.3333 6.66667" stroke="#6C63FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
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
        <aside class="sidebar" id="main-sidebar">
            <div class="logo">
                <img src="<?php echo WPVR_PLUGIN_DIR_URL . 'admin/icon/wpvr-logo.png'?>" alt="WP VR Logo" class="logo-img">
            </div>
            <nav class="nav-steps">
                <div class="nav-item" data-step="step-vertical" data-number="1"><span class="nav-text">Industry</span></div>
                <div class="nav-item" data-step="step-template" data-number="2"><span class="nav-text">Template</span></div>
                <div class="nav-item" data-step="step-preview" data-number="3"><span class="nav-text">Preview</span></div>
                <div class="nav-item" data-step="step-success" data-number="4"><span class="nav-text">Publish</span></div>
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
                <button class="btn-back"><?php echo esc_html__('BACK', 'wpvr'); ?></button>
                <div class="footer-right">
                    <button class="btn-skip" id="skip-vertical"><?php echo esc_html__('Exit', 'wpvr'); ?></button>
                    <button class="btn-primary-outline next-btn" data-next="step-template"><?php echo esc_html__('NEXT', 'wpvr'); ?></button>
                </div>
            </footer>
        </section>

        <section class="step" id="step-template">
            <div class="step-indicator centered"><?php echo esc_html(sprintf(__('STEP %d OF %d', 'wpvr'), 3, 5)); ?></div>
            <h2 class="section-title dynamic-title"><?php echo esc_html__('Show your property without another site visit', 'wpvr'); ?></h2>
            <p class="section-sub dynamic-sub"><?php echo esc_html__('Most agents publish their first property tour in minutes.', 'wpvr'); ?></p>

            <div class="template-container">
                <div class="template-preview-box" id="template-preview-box">
                    <div class="template-preview-content">
                        <div class="icon-placeholder" id="template-icon-placeholder">🏠</div>
                        <div class="template-preview-info">
                            <h3 class="template-preview-title"><?php echo esc_html__('Professional Template', 'wpvr'); ?></h3>
                            <p class="template-preview-desc"><?php echo esc_html__('Pre-configured with industry best practices and optimized settings', 'wpvr'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="upload-section" id="upload-section" style="display: none;">
                    <div class="drop-zone" id="drop-zone">
                        <input type="file" id="image-upload" accept="image/*" style="display: none;">
                        <div class="drop-zone-content">
                            <div class="drop-zone-icon">📷</div>
                            <p class="drop-zone-text"><?php echo sprintf(
                                esc_html__('Drag & drop your 360° image or %s', 'wpvr'),
                                '<span class="drop-zone-link">' . esc_html__('click to browse', 'wpvr') . '</span>'
                            ); ?></p>
                            <p class="drop-zone-hint"><?php echo esc_html__('Supports: JPG, PNG, WEBP', 'wpvr'); ?></p>
                        </div>
                    </div>
                    <a href="#" class="use-template-link" id="use-template-link"><?php echo esc_html__('USE TEMPLATE INSTEAD', 'wpvr'); ?></a>
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
                    <a href="#" class="upload-link" id="upload-link"><?php echo esc_html__('UPLOAD MY OWN 360° PHOTO', 'wpvr'); ?></a>
                    <button class="btn-primary-outline next-btn" data-next="step-preview" id="use-template-btn"><?php echo esc_html__('USE PROPERTY TEMPLATE', 'wpvr'); ?></button>
                </div>
            </div>

            <footer class="onboarding-footer">
                <button class="btn-back"><?php echo esc_html__('BACK', 'wpvr'); ?></button>
                <div class="footer-right">
                    <button class="btn-skip" id="skip-template"><?php echo esc_html__('Skip', 'wpvr'); ?></button>
                    <button class="btn-primary-outline next-btn" data-next="step-preview"><?php echo esc_html__('NEXT', 'wpvr'); ?></button>
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
                <button class="btn-back"><?php echo esc_html__('BACK', 'wpvr'); ?></button>
                <div class="footer-right">
                    <button class="btn-skip" id="skip-preview"><?php echo esc_html__('Skip', 'wpvr'); ?></button>
                    <button class="btn-primary-outline next-btn" data-next="step-success" id="publish-btn"><?php echo esc_html__('NEXT', 'wpvr'); ?></button>
                </div>
            </footer>
        </section>

        <section class="step" id="step-success">
            <div class="step-indicator centered"><?php echo esc_html(sprintf(__('STEP %d OF %d', 'wpvr'), 5, 5)); ?></div>
            <h2 class="section-title dynamic-success-title">🎉 <?php echo esc_html__('Your tour is published!', 'wpvr'); ?></h2>
            <p class="section-sub dynamic-success-sub"><?php echo esc_html__('Your virtual tour has been created and is ready to be displayed on your website.', 'wpvr'); ?></p>

            <!-- Primary CTA - Most Prominent -->
            <div class="primary-cta-container">
                <!-- <a class="btn-primary-large" id="view-tour-live-btn" target="_blank"><?php echo esc_html__('VIEW TOUR LIVE', 'wpvr'); ?></a> -->
            </div>

            <!-- Edit Tour Button -->
            <div class="edit-tour-container" style="text-align: center; margin: 24px 0;">
                <a class="btn-edit-tour" id="edit-tour-btn" style="display: inline-block; padding: 12px 32px; background-color: #fff; color: #6C63FF; font-weight: 600; border-radius: 8px; text-decoration: none; font-size: 14px; letter-spacing: 0.5px; border: 2px solid #6C63FF; cursor: pointer; transition: all 0.2s ease;"><?php echo esc_html__('EDIT YOUR TOUR', 'wpvr'); ?></a>
            </div>

            <!-- Secondary Actions - Grouped PRO Actions -->
            <div class="secondary-actions-container" style="max-width: 600px; margin: 32px auto 0; text-align: center;">
                <h3 class="secondary-actions-title" style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 20px;"><?php echo esc_html__('Enhance your tour', 'wpvr'); ?></h3>
                <div class="secondary-actions-list" style="display: flex; flex-direction: column; gap: 12px;">
                    <div class="action-item-secondary" style="display: flex; align-items: flex-start; background: #f8f9fa; border-radius: 8px; padding: 16px 20px; text-align: left;">
                        <div class="action-content" style="flex: 1;">
                            <div class="action-title" style="font-size: 15px; font-weight: 600; color: #333; margin-bottom: 4px;"><?php echo esc_html__('Unlimited Hotspots', 'wpvr'); ?> <span class="pro-badge" style="display: inline-block; background-color: #01B5FF; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; vertical-align: middle;"><?php echo esc_html__('PRO', 'wpvr'); ?></span></div>
                            <div class="action-description" style="font-size: 13px; color: #666; line-height: 1.4;"><?php echo esc_html__('Add interactive hotspots to guide visitors through your tour', 'wpvr'); ?></div>
                        </div>
                    </div>
                    <div class="action-item-secondary" style="display: flex; align-items: flex-start; background: #f8f9fa; border-radius: 8px; padding: 16px 20px; text-align: left;">
                        <div class="action-content" style="flex: 1;">
                            <div class="action-title" style="font-size: 15px; font-weight: 600; color: #333; margin-bottom: 4px;"><?php echo esc_html__('Tour Analytics', 'wpvr'); ?> <span class="pro-badge" style="display: inline-block; background-color: #01B5FF; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; vertical-align: middle;"><?php echo esc_html__('PRO', 'wpvr'); ?></span></div>
                            <div class="action-description" style="font-size: 13px; color: #666; line-height: 1.4;"><?php echo esc_html__('Track visitor engagement and see how your tour performs', 'wpvr'); ?></div>
                        </div>
                    </div>
                    <div class="action-item-secondary" style="display: flex; align-items: flex-start; background: #f8f9fa; border-radius: 8px; padding: 16px 20px; text-align: left;">
                        <div class="action-content" style="flex: 1;">
                            <div class="action-title" style="font-size: 15px; font-weight: 600; color: #333; margin-bottom: 4px;"><?php echo esc_html__('Company Logo & Description', 'wpvr'); ?> <span class="pro-badge" style="display: inline-block; background-color: #01B5FF; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; vertical-align: middle;"><?php echo esc_html__('PRO', 'wpvr'); ?></span></div>
                            <div class="action-description" style="font-size: 13px; color: #666; line-height: 1.4;"><?php echo esc_html__('Brand your tour with your logo and custom description', 'wpvr'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="onboarding-footer">
                <button class="btn-back" onclick="location.reload()"><?php echo esc_html__('START OVER', 'wpvr'); ?></button>
                <div class="footer-right">
                    <button class="btn-skip" id="skip-success"><?php echo esc_html__('Skip', 'wpvr'); ?></button>
                    <button class="btn-primary-outline"><?php echo esc_html__('FINISH', 'wpvr'); ?></button>
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