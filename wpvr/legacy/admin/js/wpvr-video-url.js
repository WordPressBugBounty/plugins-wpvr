(function (window, $) {
    'use strict';
    var __ = window.wp.i18n.__;
    var pending = null;
    var activeField = null;

    function platform(url) {
        try {
            var host = new URL(url).hostname.toLowerCase();
            if (/(^|\.)(youtube\.com|youtube-nocookie\.com|youtu\.be)$/.test(host)) return 'youtube';
            if (/(^|\.)vimeo\.com$/.test(host)) return 'vimeo';
        } catch (_) {}
        return 'selfhost';
    }

    function cancel() {
        if (pending) pending.cancel();
    }

    function verify(value) {
        var url = (value || '').trim();
        if (pending && pending.url === url) return pending.promise;
        cancel();
        var parsed;
        try { parsed = new URL(url); } catch (_) {
            return Promise.reject(new Error(__('Please enter a valid HTTP or HTTPS video URL.', 'wpvr')));
        }
        if (!/^https?:$/.test(parsed.protocol)) {
            return Promise.reject(new Error(__('Please enter a valid HTTP or HTTPS video URL.', 'wpvr')));
        }
        if (window.location.protocol === 'https:' && parsed.protocol !== 'https:') {
            return Promise.reject(new Error(__('Use an HTTPS video URL on an HTTPS site.', 'wpvr')));
        }
        if (platform(url) !== 'selfhost' || /\.(webm|ogg|ogv|mov|avi|m3u8|mpd|html?)$/i.test(parsed.pathname)) {
            return Promise.reject(new Error(__('Please use a direct 360° MP4 video URL for CDN playback.', 'wpvr')));
        }

        var video = document.createElement('video');
        var job = { url: url };
        job.promise = new Promise(function (resolve, reject) {
            var finished = false;
            var timer;
            function finish(error) {
                if (finished) return;
                finished = true;
                clearTimeout(timer);
                video.onloadeddata = video.onerror = null;
                video.pause();
                video.removeAttribute('src');
                video.load();
                if (pending === job) pending = null;
                if (error) reject(error); else resolve(url);
            }
            job.cancel = function () {
                var error = new Error(__('Video URL verification was canceled.', 'wpvr'));
                error.cancelled = true;
                finish(error);
            };
            video.crossOrigin = 'anonymous';
            video.muted = true;
            video.playsInline = true;
            video.preload = 'auto';
            video.onerror = function () {
                finish(new Error(__('Unable to load this video. Use a public MP4 link with browser-compatible encoding and CORS enabled on its host.', 'wpvr')));
            };
            video.onloadeddata = function () {
                if (video.readyState < 2 || !video.videoWidth || !video.videoHeight) return;
                var gl;
                var texture;
                try {
                    var canvas = document.createElement('canvas');
                    canvas.width = canvas.height = 1;
                    gl = canvas.getContext('webgl');
                    if (!gl) throw new Error(__('WebGL is unavailable. Enable WebGL to verify and display 360° videos.', 'wpvr'));
                    texture = gl.createTexture();
                    gl.bindTexture(gl.TEXTURE_2D, texture);
                    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, video);
                    if (gl.getError() !== gl.NO_ERROR) throw new Error(__('This video cannot be used for 360° rendering. Check its encoding, dimensions and CDN CORS settings.', 'wpvr'));
                    finish();
                } catch (error) {
                    finish(new Error(error.name === 'SecurityError'
                        ? __('The video host blocked cross-origin access. Enable CORS for this site on the CDN.', 'wpvr')
                        : error.message));
                } finally {
                    if (gl) {
                        if (texture) gl.deleteTexture(texture);
                        var extension = gl.getExtension('WEBGL_lose_context');
                        if (extension) extension.loseContext();
                    }
                }
            };
            timer = setTimeout(function () {
                finish(new Error(__('Video verification timed out. Check the URL and CDN access, then retry.', 'wpvr')));
            }, 15000);
            video.src = url;
            video.load();
        });
        pending = job;
        return job.promise;
    }

    window.WPVRVideoUrl = {
        platform: platform,
        verify: verify,
        cancel: cancel,
        configurePlayer: function (player) {
            player.on('error', function () {
                if (player.errorDisplay && typeof player.errorDisplay.content === 'function') {
                    player.errorDisplay.content(__('Unable to play this video. Check that its URL is accessible, the encoding is supported and the video host allows CORS.', 'wpvr'));
                }
            });
            return player;
        },
        registerField: function (field) {
            activeField = field;
            return function () { if (activeField === field) activeField = null; cancel(); };
        },
        flush: function () { return activeField ? activeField.apply() : Promise.resolve(true); },
        disposePreview: function (container) {
            $(container).find('video').each(function () {
                var player = window.videojs && window.videojs.getPlayer && window.videojs.getPlayer(this.id);
                if (player) {
                    if (player.pnlmViewer && typeof player.pnlmViewer.destroy === 'function') player.pnlmViewer.destroy();
                    player.dispose();
                }
            });
        },
        deferLegacyAction: function (event, button) {
            if (!activeField || !activeField.isDirty || !activeField.isDirty()) return false;
            event.preventDefault();
            event.stopImmediatePropagation();
            if ($(button).data('wpvrVideoPending')) return true;
            $(button).data('wpvrVideoPending', true);
            activeField.apply().then(function (valid) {
                $(button).removeData('wpvrVideoPending');
                if (valid) $(button).trigger('click');
            });
            return true;
        }
    };
    window.addEventListener('pagehide', cancel);

    // The Free renderer supplies these controls for both Free and Pro old editors.
    $(function () {
        var $source = $('.wpvr-video-source-select');
        if (!$source.length) return;
        var $root = $source.closest('.wpvr-video-source-field');
        var $value = $root.find('.video-attachment-url');
        var $draft = $root.find('.wpvr-video-cdn-url');
        var $apply = $root.find('.wpvr-video-apply-url');
        var $feedback = $root.find('.wpvr-video-url-feedback');
        var generation = 0;

        function clearPreview() {
            var $preview = $('.iframe-wrapper .pano-wrap');
            window.WPVRVideoUrl.disposePreview($preview);
            $preview.empty();
        }
        function feedback(message, error) {
            $feedback.text(message).toggleClass('is-error', !!error);
        }
        function isDirty() {
            return $("input[name='panovideo']:checked").val() === 'on' && $source.val() === 'cdn'
                && ($draft.val() || '').trim() !== ($value.val() || '');
        }
        function apply(force) {
            if (!force && !isDirty()) return Promise.resolve(true);
            var url = ($draft.val() || '').trim();
            var currentGeneration = generation;
            $apply.prop('disabled', true);
            feedback(__('Verifying video…', 'wpvr'));
            return verify(url).then(function (accepted) {
                if (currentGeneration !== generation || $source.val() !== 'cdn') return false;
                $value.val(accepted).trigger('change');
                $draft.val(accepted);
                $root.find('.wpvr-video-remove-url').show();
                feedback(__('Video URL applied.', 'wpvr'));
                return true;
            }).catch(function (error) {
                if (currentGeneration === generation && !error.cancelled) feedback(error.message, true);
                return false;
            }).finally(function () {
                if (currentGeneration === generation) $apply.prop('disabled', false);
            });
        }
        function render() {
            var cdn = $source.val() === 'cdn';
            var enabled = $("input[name='panovideo']:checked").val() === 'on';
            $value.attr('type', cdn ? 'hidden' : 'url').attr('placeholder', $source.val() === 'url'
                ? 'https://www.youtube.com/watch?v=…' : '');
            $value.prop('disabled', !enabled);
            $root.find('.wpvr-video-cdn-controls').toggle(cdn);
            $draft.prop('disabled', !cdn || !enabled);
            $root.find('.video-upload').toggle($source.val() === 'upload');
            $root.find('.wpvr-video-remove-url').toggle(!!$value.val());
        }
        $draft.val($value.val());
        render();
        $source.on('change', function () {
            generation++;
            cancel();
            clearPreview();
            $value.val('').trigger('change');
            $draft.val('');
            $apply.prop('disabled', false);
            feedback('');
            render();
        });
        $draft.on('input', function () {
            generation++;
            cancel();
            $apply.prop('disabled', false);
            feedback('');
        });
        $apply.on('click', function (event) { event.preventDefault(); apply(true); });
        $root.find('.wpvr-video-remove-url').on('click', function () {
            generation++;
            cancel();
            clearPreview();
            $value.val('').trigger('change');
            $draft.val('');
            $apply.prop('disabled', false);
            feedback('');
            render();
        });
        $("input[name='panovideo']").on('change', function () { generation++; cancel(); $apply.prop('disabled', false); render(); });
        window.WPVRVideoUrl.registerField({ apply: apply, isDirty: isDirty });
    });
})(window, jQuery);
