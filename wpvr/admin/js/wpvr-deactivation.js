(function ($) {
    'use strict';

    function init(modal) {
        // Auto-select first reason card.
        var $firstLi    = modal.find('ul.wd-de-reasons li').first();
        var $firstRadio = $firstLi.find('input[type="radio"]');
        $firstRadio.prop('checked', true);
        $firstLi.addClass('wd-de-reason-selected');
        modal.find('.wd-dr-modal-reason-input').show();
        modal.find('.wd-dr-modal-reason-input textarea').attr('placeholder', $firstLi.data('placeholder'));

        // Narrow modal.
        modal.find('.wd-dr-modal-wrap').css({ 'max-width': '700px', 'border-radius': '8px' });

        // Focus textarea when modal opens.
        if ( modal[0] && window.MutationObserver ) {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if ( mutation.attributeName === 'class' && modal.hasClass('modal-active') ) {
                        modal.find('.wd-dr-modal-reason-input textarea').focus();
                    }
                });
            });
            observer.observe(modal[0], { attributes: true });
        }

        // De-emphasise "Skip & Deactivate".
        modal.find('a.dont-bother-me').css({
            'font-size': '11px',
            'color':     '#a0aec0',
            'border':    'none',
            'padding':   '5px 6px'
        });
    }

    $(function () {
        var modal = $('#wpvr-wd-dr-modal');
        if (modal.length) {
            init(modal);
        }
    });

}(jQuery));
