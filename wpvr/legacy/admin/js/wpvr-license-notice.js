(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var dismissButton = event.target.closest('#wpvr-license-fallback-notice .notice-dismiss');

        if (!dismissButton) {
            return;
        }

        var notice = dismissButton.closest('#wpvr-license-fallback-notice');

        if (notice) {
            notice.remove();
        }
    });
}());
