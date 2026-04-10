(function($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

})(jQuery);
function wpvrhotspot(hotSpotDiv, hotspotData) {
    const args = hotspotData.on_click_content;

    if (args) {
        const hasTextContent = args.replace(/<[^>]*>/g, '').trim() !== '';
        const hasMediaContent = /<(img|video|audio|iframe|embed|object)\b[^>]*>/i.test(args);
        const hasOtherContent = args.replace(/<(p|br|div|span)\b[^>]*\/?>/gi, '').trim() !== '';

        if (hasTextContent || hasMediaContent || hasOtherContent) {
            const cleanArgs = args.replace(/\\/g, '');

            const $wrapper = jQuery(hotSpotDiv.target).parent().siblings(".custom-ifram-wrapper");
            $wrapper.find('.custom-ifram').html(cleanArgs);
            $wrapper.fadeIn();
            jQuery(hotSpotDiv.target).closest(".pano-wrap").addClass("show-modal");
        }
    }
}

function wpvrtooltip(hotSpotDiv, args) {
    if (args) {
        const hasTextContent = args.replace(/<[^>]*>/g, '').trim() !== '';
        const hasMediaContent = /<(img|video|audio|iframe|embed|object)\b[^>]*>/i.test(args);
        const hasOtherContent = args.replace(/<(p|br|div|span)\b[^>]*\/?>/gi, '').trim() !== '';

        if (hasTextContent || hasMediaContent || hasOtherContent) {
            const cleanArgs = args.replace(/\\/g, '');

            hotSpotDiv.classList.add('custom-tooltip');
            const p = document.createElement('p');
            p.innerHTML = cleanArgs;
            hotSpotDiv.appendChild(p);

            p.style.marginLeft = -(p.scrollWidth - hotSpotDiv.offsetWidth) / 2 + 'px';
            p.style.marginTop = -p.scrollHeight - 12 + 'px';

            // Optional: inject style once
            if (!document.getElementById('wpvr-tooltip-style')) {
                const style = document.createElement('style');
                style.id = 'wpvr-tooltip-style';
                style.textContent = `
                    .table, .table td, .table th {
                        border: 1px solid #dee2e6;
                        border-collapse: collapse;
                        padding: 8px;
                    }
                `;
                document.head.appendChild(style);
            }
        }
    }
}

jQuery(document).ready(function($) {

    $(".cross").on("click", function(e) {
        e.preventDefault();
        $(this).parent(".custom-ifram-wrapper").fadeOut();
        $(this).parents(".pano-wrap").removeClass("show-modal");

        $('.vr-iframe').attr('src', '');
        // $('iframe').attr('src', $('iframe').attr('src'));
        if ($('#wpvr-video').length != 0) {
            $('#wpvr-video').get(0).pause();
        }

        $(this).parent(".custom-ifram-wrapper").find('.custom-ifram').empty();

    });

});

jQuery(document).ready(function($) {

    var notice_active = wpvr_public.notice_active;
    var notice = wpvr_public.notice;
    if (notice_active == "true") {
        if (!$.cookie("wpvr_mobile_notice")) {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                if ($(".pano-wrap")[0]) {
                    $('body').append("<div class='wpvr-mobile-notice'><p>" + notice + "</p> <span class='notice-close'><i class='fa fa-times'></i></span></div>");
                }
            }
        }
    }

    $('.wpvr-mobile-notice .notice-close').on('click', function() {
        $('.wpvr-mobile-notice').fadeOut();
        $.cookie('wpvr_mobile_notice', 'true');
    });
});

// WPVR Hotspot Long-Press Mobile Support
jQuery(document).ready(function($) {
    var isTouchDevice = (("ontouchstart" in window) || (navigator.maxTouchPoints > 0));
    if (isTouchDevice) {
        setTimeout(function() {
            if (!$('.wpvr-hotspot-longpress-alert').length) {
                alert('Tip: On mobile devices, long-press a hotspot to show its hover content.');
            }
            $('.pnlm-hotspot-base, .pnlm-hotspot').each(function() {
                $(this).off('mouseenter mouseover mouseleave');

                // Hide only the inner content <p>, not the hotspot pointer
                $(this).find('p').hide();

                var longPressTimer;
                var isLongPress = false;

                // Prevent click after long press
                $(this).off('click._wpvrHotspotFix').on('click._wpvrHotspotFix', function(e) {
                    if ($(this).data('wpvr-longpress')) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        $(this).data('wpvr-longpress', false);
                        return false;
                    }
                });

                $(this).on('touchstart', function(e) {
                    isLongPress = false;
                    var $hotspot = $(this);

                    // Hide inner content on touch start
                    $hotspot.find('p').hide();

                    longPressTimer = setTimeout(function() {
                        isLongPress = true;
                        $hotspot.data('wpvr-longpress', true);

                        // Hide all other hotspot contents first
                        $('.pnlm-hotspot-base p, .pnlm-hotspot p').hide();

                        // Show only this hotspot's content
                        $hotspot.find('p').show();
                    }, 600);
                });

                $(this).on('touchend', function(e) {
                    clearTimeout(longPressTimer);
                    if (isLongPress) {
                        e.preventDefault();
                        e.stopPropagation();
                        // Do NOT auto-hide after 2 seconds anymore
                        // Content will stay open until user taps elsewhere
                    } else {
                        // Short tap: hide inner content, let click fire
                        $(this).find('p').hide();
                    }
                });

                $(this).on('touchmove', function() {
                    clearTimeout(longPressTimer);
                    isLongPress = false;
                    // Hide inner content if user starts panning
                    $(this).find('p').hide();
                });
            });

            // Hide hotspot content when tapping outside any hotspot
            $(document).on('touchstart.wpvrHotspotOutside', function(e) {
                // If the tap is not inside a hotspot or its content
                if (!$(e.target).closest('.pnlm-hotspot-base, .pnlm-hotspot').length) {
                    $('.pnlm-hotspot-base p, .pnlm-hotspot p').hide();
                }
            });

            // Hide other hotspot contents when another hotspot is long-pressed
            $('.pnlm-hotspot-base, .pnlm-hotspot').on('touchstart', function(e) {
                // Hide all other hotspot contents except the one being touched
                var $current = $(this);
                $('.pnlm-hotspot-base, .pnlm-hotspot').not($current).find('p').hide();
            });
        }, 800);
    }
});



