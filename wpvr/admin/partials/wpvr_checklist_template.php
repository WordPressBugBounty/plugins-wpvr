<div id="wpvr-checklist">
    <div class="wpvr-progress-container">
        <div id="wpvr-progress-bar"></div>
        <span id="wpvr-progress-text">0%</span>
    </div>


    <ul id="wpvr-checklist-items" class="wpvr-checklist">

        <li class="wpvr-checklist-item" style="color: <?php echo esc_attr($wpvr_check_scene ? '#0E003C' : '#73707D'); ?>">
            <label for="wpvr-check-scene" class="wpvr-custom-checkbox">
                <input type="checkbox" id="wpvr-check-scene" class="wpvr-checklist-items"
                    <?php echo esc_attr($wpvr_check_scene ? 'checked' : '');?>>
                <span class="checkmark"></span>
                <?php _e('Add at least one scene', 'wpvr'); ?>
            </label>

            <span class="wpvr-tooltip">
                        <span class="icon">
                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                      d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                                </path>
                            </svg>
                        </span>
                        <p><?php _e('Upload your first 360° image or video.','wpvr')?></p>
                    </span>
        </li>

        <li class="wpvr-checklist-item" style="color: <?php echo esc_attr($wpvr_check_media ? '#0E003C' : '#73707D'); ?>">
            <label for="wpvr-check-media" class="wpvr-custom-checkbox">
                <input type="checkbox" id="wpvr-check-media" class="wpvr-checklist-items"
                    <?php echo esc_attr($wpvr_check_media ? 'checked' : ''); ?>>
                <span class="checkmark"></span>
                <?php _e('Upload 360° media', 'wpvr'); ?>
            </label>

            <span class="wpvr-tooltip">
                        <span class="icon">
                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                      d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                                </path>
                            </svg>
                        </span>
                        <p><?php _e('Ensure media is self-hosted or from YouTube/Vimeo.','wpvr')?></p>
                    </span>
        </li>

        <li class="wpvr-checklist-item" style="color: <?php echo esc_attr($wpvr_check_default ? '#0E003C' : '#73707D'); ?>">
            <label for="wpvr-check-default" class="wpvr-custom-checkbox">
                <input type="checkbox" id="wpvr-check-default" class="wpvr-checklist-items"
                    <?php echo esc_attr($wpvr_check_default ? 'checked' : ''); ?>>
                <span class="checkmark"></span>
                <?php _e('Set a default scene', 'wpvr'); ?>
            </label>

            <span class="wpvr-tooltip">
                        <span class="icon">
                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                      d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                                </path>
                            </svg>
                        </span>
                        <p><?php _e('Choose where users will start the tour.','wpvr')?></p>
                    </span>
        </li>

        <li class="wpvr-checklist-item" style="color: <?php echo esc_attr($wpvr_check_hotspots ? '#0E003C' : '#73707D'); ?>">
            <label for="wpvr-check-hotspots" class="wpvr-custom-checkbox">
                <input type="checkbox" id="wpvr-check-hotspots" class="wpvr-checklist-items"
                    <?php echo esc_attr($wpvr_check_hotspots ? 'checked' : ''); ?>>
                <span class="checkmark"></span>
                <?php _e('Add navigation hotspots', 'wpvr'); ?>
            </label>

            <span class="wpvr-tooltip">
                        <span class="icon">
                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                      d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                                </path>
                            </svg>
                        </span>
                        <p><?php _e('Link different scenes or add interactive elements.','wpvr')?></p>
                    </span>
        </li>

        <li class="wpvr-checklist-item" style="color: <?php echo esc_attr($wpvr_check_movement_controls  ? '#0E003C' : '#73707D'); ?>">
            <label for="wpvr-check-movement-controls" class="wpvr-custom-checkbox">
                <input type="checkbox" id="wpvr-check-movement-controls" class="wpvr-checklist-items"
                    <?php echo esc_attr($wpvr_check_movement_controls ? 'checked' : ''); ?>>
                <span class="checkmark"></span>
                <?php _e('Enable basic controls', 'wpvr'); ?>
            </label>

            <span class="wpvr-tooltip">
                        <span class="icon">
                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                      d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                                </path>
                            </svg>
                        </span>
                        <p><?php _e('Allow movement within the tour.','wpvr')?></p>
                    </span>
        </li>
        <?php  if (apply_filters('is_wpvr_pro_active', false)) {?>
            <li class="wpvr-checklist-item">
                <label for="wpvr-check-zoom-controls" class="wpvr-custom-checkbox">
                    <input type="checkbox" id="wpvr-check-zoom-controls" class="wpvr-checklist-items"
                        <?php echo esc_attr($wpvr_check_zoom_controls ? 'checked' : ''); ?>>
                    <span class="checkmark"></span>
                    <?php _e('Enable zoom controls', 'wpvr'); ?>
                </label>

                <span class="wpvr-tooltip">
                    <span class="icon">
                        <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                            <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                  d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                            </path>
                        </svg>
                    </span>
                    <p><?php _e('Allow zooming within the tour.','wpvr')?></p>
                </span>
            </li>
        <?php }?>

        <li class="wpvr-checklist-item" style="color: <?php echo esc_attr($wpvr_check_publish ? '#0E003C' : '#73707D'); ?>">
            <label for="wpvr-check-publish" class="wpvr-custom-checkbox">
                <input type="checkbox" id="wpvr-check-publish" class="wpvr-checklist-items"
                    <?php echo esc_attr($wpvr_check_publish ? 'checked' : ''); ?>>
                <span class="checkmark"></span>
                <?php _e('Publish the tour', 'wpvr'); ?>
            </label>

            <span class="wpvr-tooltip">
                        <span class="icon">
                            <svg width="15" height="16" fill="none" viewBox="0 0 15 16" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="#73707D" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.333"
                                      d="M5.56 5.9a2.08 2.08 0 01.873-1.114 1.92 1.92 0 011.351-.259 1.98 1.98 0 011.192.717c.305.38.471.86.47 1.356 0 1.4-2 2.1-2 2.1m.054 2.8h.006m6.66-3.5c0 3.866-2.984 7-6.666 7C3.818 15 .833 11.866.833 8S3.818 1 7.5 1s6.666 3.134 6.666 7z">
                                </path>
                            </svg>
                        </span>
                        <p><?php _e('Publish your tour and embed it anywhere on your site.','wpvr')?></p>
                    </span>
        </li>

    </ul>

</div>