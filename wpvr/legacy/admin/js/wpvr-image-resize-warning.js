(function (window, $) {
    'use strict';

    var modalSelector = '.wpvr-image-resize-modal';

    function getConfig() {
        if (window.wpvr_obj && window.wpvr_obj.image_resize_warning) {
            return window.wpvr_obj;
        }

        return window.wpvrSetupWizardData || {};
    }

    function remove() {
        $(modalSelector).remove();
        $(document).off('keydown.wpvrImageResizeWarning');
    }

    function enableLargeImageHandler() {
        var config = getConfig();

        if (!config.ajaxurl || !config.ajax_nonce) {
            return;
        }

        $.post(config.ajaxurl, {
            action: 'wpvr_enable_large_image_handler',
            nonce: config.ajax_nonce
        });

        config.image_resize_warning.setting_enabled = true;
    }

    function warningIcon() {
        return '<svg width="36" height="30" viewBox="0 0 36 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
            '<path fill-rule="evenodd" clip-rule="evenodd" d="M21.4802 1.86951L35.4553 24.3914C36.1816 25.5621 36.1816 26.9598 35.4553 28.1305C34.7289 29.3011 33.4278 30 31.9751 30H4.025C2.5722 30 1.2712 29.3011 0.5448 28.1305C-0.1816 26.9598 -0.1816 25.5621 0.5448 24.3914L14.5198 1.86951C15.2462 0.698907 16.5473 0 18 0C19.4527 0 20.7538 0.698841 21.4802 1.86951ZM31.9751 28.0358C32.6647 28.0358 33.2822 27.7041 33.6271 27.1484C33.9719 26.5927 33.9719 25.9291 33.6271 25.3734L19.6521 2.8515C19.3072 2.2958 18.6896 1.96404 18 1.96404C17.3104 1.96404 16.6928 2.2958 16.3479 2.8515L2.37293 25.3734C2.02812 25.9291 2.02812 26.5927 2.37293 27.1484C2.71774 27.7041 3.33537 28.0358 4.025 28.0358H31.9751Z" fill="#FAAC14"/>' +
            '<path d="M18 22C17.4486 22 17 22.4486 17 23C17 23.5514 17.4486 24 18 24C18.5514 24 19 23.5514 19 23C19 22.4486 18.5514 22 18 22Z" fill="#FAAC14"/>' +
            '<rect x="17" y="11" width="2" height="9" fill="#FAAC14"/>' +
            '</svg>';
    }

    function showModal(attachment, applyImage) {
        var config = getConfig();
        var strings = config.image_resize_warning || {};
        var $modal = $('<div>', {
            class: 'wpvr-image-resize-modal',
            role: 'dialog',
            'aria-modal': 'true',
            'aria-labelledby': 'wpvr-image-resize-modal-title'
        });
        var $setting = $('<input>', {
            type: 'checkbox',
            id: 'wpvr-disable-large-image-handler',
            checked: !!strings.setting_enabled
        });
        var completed = false;

        function finish(imageUrl, enableSetting) {
            if (completed) {
                return;
            }

            completed = true;
            if (enableSetting) {
                enableLargeImageHandler();
            }

            remove();
            applyImage(imageUrl);
        }

        $setting.on('change', function () {
            if (this.checked) {
                finish(attachment.originalImageURL, true);
            }
        });

        var $options = $('<div>', { class: 'wpvr-image-resize-modal__options' });

        if (strings.can_manage_settings) {
            $options.append(
                $('<label>', {
                    for: 'wpvr-disable-large-image-handler',
                    class: 'wpvr-image-resize-modal__setting'
                }).append(
                    $setting,
                    $('<span>', {
                        class: 'wpvr-image-resize-modal__toggle',
                        'aria-hidden': 'true'
                    }),
                    $('<span>', { text: strings.disable_handler || '' })
                ),
                $('<p>', {
                    class: 'wpvr-image-resize-modal__note',
                    text: strings.high_resolution_note || ''
                })
            );
        }

        var $body = $('<div>', { class: 'pano-error-body' }).append(
            $('<button>', {
                type: 'button',
                class: 'cross wpvr-image-resize-modal__close',
                'aria-label': strings.close || 'Close',
                text: '\u00d7'
            }).on('click', function () {
                finish(attachment.url);
            }),
            $('<span>', { class: 'icon pano-warning' }).html(warningIcon()),
            $('<div>', { class: 'pano-error-message' }).append(
                $('<h3>', {
                    id: 'wpvr-image-resize-modal-title',
                    class: 'pano-error-title',
                    text: strings.heading || ''
                }),
                $('<p>', {
                    text: strings.scaled_message || ''
                })
            ),
            $options
        );

        remove();
        $('body').append(
            $modal.append(
                $('<div>', { class: 'pano-error-wrapper' }).append($body)
            )
        );

        $(document).on('keydown.wpvrImageResizeWarning', function (event) {
            if (event.key === 'Escape') {
                finish(attachment.url);
            }
        });

        $modal.on('click', function (event) {
            if (event.target === this) {
                finish(attachment.url);
            }
        });

        if (strings.can_manage_settings) {
            $setting.trigger('focus');
        } else {
            $modal.find('.wpvr-image-resize-modal__close').trigger('focus');
        }
    }

    function applySelection(attachment, applyImage) {
        var config = getConfig();
        var strings = config.image_resize_warning || {};
        var selectedUrl = attachment && attachment.url;
        var originalUrl = attachment && attachment.originalImageURL;

        if (!selectedUrl || typeof applyImage !== 'function') {
            return;
        }

        if (originalUrl && originalUrl !== selectedUrl && strings.setting_enabled) {
            applyImage(originalUrl);
            return;
        }

        if (originalUrl && originalUrl !== selectedUrl) {
            showModal(attachment, applyImage);
            return;
        }

        applyImage(selectedUrl);
    }

    function handleSelection(attachment, applyImage) {
        var attachmentId = parseInt(attachment && attachment.id, 10) || 0;
        var width = parseInt(attachment && attachment.width, 10) || 0;
        var height = parseInt(attachment && attachment.height, 10) || 0;
        var needsRefresh = !(attachment && attachment.originalImageURL)
            && (width >= 2560 || height >= 2560)
            && attachmentId
            && window.wp
            && wp.media;

        remove();

        if (!needsRefresh) {
            applySelection(attachment, applyImage);
            return;
        }

        var attachmentModel = wp.media.attachment(attachmentId);

        attachmentModel.fetch()
            .done(function () {
                try {
                    applySelection(attachmentModel.toJSON(), applyImage);
                } catch (error) {
                    applyImage(attachment.url);
                }
            })
            .fail(function () {
                applySelection(attachment, applyImage);
            });
    }

    window.WPVRImageResizeWarning = {
        handleSelection: handleSelection,
        remove: remove
    };
}(window, jQuery));
