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

            jQuery(document).ready(function ($) {
                const $wrapper = $(hotSpotDiv.target).parent().siblings(".custom-ifram-wrapper");
                $wrapper.find('.custom-ifram').html(cleanArgs);
                $wrapper.fadeToggle();
                $(hotSpotDiv.target).closest(".pano-wrap").toggleClass("show-modal");
            });
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


function wpvrSanitizeHTML(html) {
    if (!html || typeof html !== 'string') {
        return '';
    }

    // Remove dangerous tags completely
    html = html.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '');
    html = html.replace(/<iframe[^>]*>[\s\S]*?<\/iframe>/gi, '');
    html = html.replace(/<object[^>]*>[\s\S]*?<\/object>/gi, '');
    html = html.replace(/<embed[^>]*>[\s\S]*?<\/embed>/gi, '');
    html = html.replace(/<applet[^>]*>[\s\S]*?<\/applet>/gi, '');
    html = html.replace(/<form[^>]*>[\s\S]*?<\/form>/gi, '');
    html = html.replace(/<input[^>]*>/gi, '');
    html = html.replace(/<textarea[^>]*>[\s\S]*?<\/textarea>/gi, '');
    html = html.replace(/<select[^>]*>[\s\S]*?<\/select>/gi, '');
    html = html.replace(/<button[^>]*>[\s\S]*?<\/button>/gi, '');
    
    // Remove event handlers (on* attributes)
    html = html.replace(/\s+on\w+\s*=\s*["'][^"']*["']/gi, '');
    html = html.replace(/\s+on\w+\s*=\s*[^\s>"']+/gi, '');
    
    // Remove dangerous protocols
    html = html.replace(/javascript\s*:/gi, '');
    html = html.replace(/vbscript\s*:/gi, '');
    html = html.replace(/data\s*:/gi, '');
    html = html.replace(/about\s*:/gi, '');
    
    // Remove style attributes that could contain javascript
    html = html.replace(/\s+style\s*=\s*["'][^"']*expression\s*\([^"']*\)[^"']*["']/gi, '');
    html = html.replace(/\s+style\s*=\s*["'][^"']*javascript\s*:[^"']*["']/gi, '');
    
    return html;
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



