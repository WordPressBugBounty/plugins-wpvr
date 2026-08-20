(function ($) {
    'use strict';

    var defaults = {
        tour: 'Virtual tour',
        instructions: 'Use the arrow keys or W, A, S, and D to look around. Use Tab to reach tour controls and hotspots.',
        load: 'Load virtual tour',
        hotspot: 'Tour hotspot',
        scene: 'View scene',
        scene_menu: 'Scene menu',
        previous_scene: 'Previous scene',
        next_scene: 'Next scene',
        pan_up: 'Look up',
        pan_down: 'Look down',
        pan_left: 'Look left',
        pan_right: 'Look right',
        zoom_in: 'Zoom in',
        zoom_out: 'Zoom out',
        fullscreen: 'Toggle fullscreen',
        home: 'Return to starting scene',
        orientation: 'Toggle device orientation',
        audio: 'Toggle tour audio',
        gallery: 'Toggle scene gallery',
        explainer: 'Open tour video',
        form: 'Open form',
        floor_plan: 'Open floor plan',
        floor_plan_point: 'View scene from floor plan',
        company_info: 'Company information',
        close: 'Close dialog',
        dialog: 'Tour dialog'
    };
    var labels = $.extend({}, defaults, window.wpvrAccessibility || {});
    var dialogSelector = [
        '.explainer',
        '.wpvr-generic-form',
        '.wpvr-floor-map',
        '.custom-ifram-wrapper',
        '.wpvr-hotspot-tweak-contents-wrapper'
    ].join(',');
    var controlSelector = [
        '.pnlm-load-button',
        '.pnlm-zoom-in',
        '.pnlm-zoom-out',
        '.pnlm-fullscreen-toggle-button',
        '.pnlm-orientation-button',
        '.ctrl',
        '.explainer_button',
        '.fullscreen-button',
        '.vrgcontrols',
        '.generic_form_button',
        '.floor_map_button',
        '.custom-scene-navigation',
        '.scene-navigation-list',
        '.floor-plan-pointer',
        '.scctrl',
        '.wpvr_owl_prev',
        '.wpvr_owl_next',
        '.close-explainer-video',
        '.close-generic-form',
        '.close-floor-map-plan',
        '.cross',
        '.cp-logo-ctrl'
    ].join(',');
    var focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        'iframe',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');
    var enhancedControls = new WeakSet();
    var enhancedDialogs = new WeakSet();
    var externalHotspotLinks = new WeakMap();
    var dialogStates = new WeakMap();
    var pendingOpeners = new WeakMap();
    var movementStates = new WeakMap();
    var tooltipIndex = 0;
    var scanQueued = false;

    function isVisible(element) {
        if (!element || element.hidden) {
            return false;
        }

        var current = element;
        while (current && current.nodeType === 1) {
            var style = window.getComputedStyle(current);
            if (style.display === 'none' || style.visibility === 'hidden') {
                return false;
            }
            current = current.parentElement;
        }
        return true;
    }

    function getTour(element) {
        return element && element.closest ? element.closest('.pano-wrap') : null;
    }

    function getViewer(container) {
        if (!container || !window.wpvrViewers) {
            return null;
        }
        return window.wpvrViewers[container.id] || null;
    }

    function instructionsId(container) {
        return container.id + '-keyboard-instructions';
    }

    function enhanceContainer(container) {
        if (!container.id || container.getAttribute('data-wpvr-a11y') === 'true') {
            return;
        }

        container.setAttribute('data-wpvr-a11y', 'true');
        container.setAttribute('tabindex', '0');
        container.setAttribute('role', 'region');
        if (!container.getAttribute('aria-label')) {
            container.setAttribute('aria-label', labels.tour);
        }

        var instructions = document.createElement('span');
        instructions.id = instructionsId(container);
        instructions.className = 'wpvr-a11y-instructions';
        instructions.textContent = labels.instructions;
        container.insertBefore(instructions, container.firstChild);
        container.setAttribute('aria-describedby', instructions.id);
        container.addEventListener('keydown', handleViewerKeydown, true);
        container.addEventListener('keyup', handleViewerKeyup, true);
        container.addEventListener('blur', stopViewerMovement, true);
        container.addEventListener('focusin', keepRenderContainerAligned, true);
    }

    function keepRenderContainerAligned(event) {
        var viewerContainer = event.currentTarget;
        var renderContainer = event.target.closest
            ? event.target.closest('.pnlm-render-container')
            : null;

        if (!viewerContainer || !renderContainer) {
            return;
        }

        function resetScrollPosition() {
            if (viewerContainer.scrollLeft || viewerContainer.scrollTop) {
                viewerContainer.scrollLeft = 0;
                viewerContainer.scrollTop = 0;
            }
            if (renderContainer.scrollLeft || renderContainer.scrollTop) {
                renderContainer.scrollLeft = 0;
                renderContainer.scrollTop = 0;
            }
        }

        resetScrollPosition();
        window.requestAnimationFrame(resetScrollPosition);
        window.setTimeout(resetScrollPosition, 0);
    }

    function handleViewerKeydown(event) {
        var container = event.currentTarget;
        if (event.target !== container || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        var key = event.key.toLowerCase();
        var viewer = getViewer(container);
        var handled = true;
        var step = event.shiftKey ? 15 : 5;

        if (key === 'enter' || key === ' ') {
            var loadButton = container.querySelector('.pnlm-load-button');
            if (loadButton && isVisible(loadButton)) {
                loadButton.click();
                focusTourAfterLoad(container);
            } else {
                handled = false;
            }
        } else if (!viewer) {
            handled = false;
        } else if (isMovementKey(key)) {
            stopAutoRotate(viewer);
            startViewerMovement(container, key, event.shiftKey);
        } else if (key === '+' || key === '=') {
            stopAutoRotate(viewer);
            viewer.setHfov(viewer.getHfov() - step, false);
        } else if (key === '-' || key === '_') {
            stopAutoRotate(viewer);
            viewer.setHfov(viewer.getHfov() + step, false);
        } else {
            handled = false;
        }

        if (handled) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }

    function isMovementKey(key) {
        return key === 'arrowup' || key === 'w' ||
            key === 'arrowdown' || key === 's' ||
            key === 'arrowleft' || key === 'a' ||
            key === 'arrowright' || key === 'd';
    }

    function movementState(container) {
        var state = movementStates.get(container);
        if (!state) {
            state = {
                keys: {},
                fast: false,
                frame: null,
                lastFrame: null
            };
            movementStates.set(container, state);
        }
        return state;
    }

    function startViewerMovement(container, key, fast) {
        var state = movementState(container);
        state.keys[key] = true;
        state.fast = fast;

        if (state.frame !== null) {
            return;
        }

        state.lastFrame = null;
        state.frame = window.requestAnimationFrame(function move(timestamp) {
            var viewer = getViewer(container);
            var current = movementState(container);
            var moving = Object.keys(current.keys).some(function (pressedKey) {
                return current.keys[pressedKey];
            });

            if (!viewer || !moving || document.activeElement !== container) {
                current.frame = null;
                current.lastFrame = null;
                return;
            }

            if (current.lastFrame === null) {
                current.lastFrame = timestamp;
            }
            var elapsed = Math.min(timestamp - current.lastFrame, 50);
            var distance = (current.fast ? 90 : 45) * elapsed / 1000;
            var pitchDirection =
                (current.keys.arrowup || current.keys.w ? 1 : 0) -
                (current.keys.arrowdown || current.keys.s ? 1 : 0);
            var yawDirection =
                (current.keys.arrowright || current.keys.d ? 1 : 0) -
                (current.keys.arrowleft || current.keys.a ? 1 : 0);

            current.lastFrame = timestamp;
            if (pitchDirection) {
                viewer.setPitch(viewer.getPitch() + pitchDirection * distance, false);
            }
            if (yawDirection) {
                viewer.setYaw(viewer.getYaw() + yawDirection * distance, false);
            }
            current.frame = window.requestAnimationFrame(move);
        });
    }

    function handleViewerKeyup(event) {
        var container = event.currentTarget;
        var key = event.key.toLowerCase();
        var state = movementStates.get(container);

        if (key === 'shift' && state) {
            state.fast = false;
            return;
        }
        if (!isMovementKey(key) || !state) {
            return;
        }

        state.keys[key] = false;
        event.preventDefault();
        event.stopImmediatePropagation();
    }

    function stopViewerMovement(event) {
        var container = event.currentTarget || event;
        var state = movementStates.get(container);
        if (!state) {
            return;
        }
        state.keys = {};
        state.lastFrame = null;
        if (state.frame !== null) {
            window.cancelAnimationFrame(state.frame);
            state.frame = null;
        }
    }

    function stopAutoRotate(viewer) {
        if (typeof viewer.stopAutoRotate === 'function') {
            viewer.stopAutoRotate();
        }
    }

    function focusTourAfterLoad(container) {
        window.setTimeout(function () {
            container.focus();
        }, 50);
        window.setTimeout(function () {
            if (!container.contains(document.activeElement) || document.activeElement === document.body) {
                container.focus();
            }
        }, 500);
    }

    function labelFor(element) {
        var title = element.getAttribute('aria-label') || element.getAttribute('title');
        if (title) {
            return title;
        }
        if (element.matches('.pnlm-load-button')) {
            return labels.load;
        }
        if (element.matches('.pnlm-zoom-in, [id^="zoom-in"]')) {
            return labels.zoom_in;
        }
        if (element.matches('.pnlm-zoom-out, [id^="zoom-out"]')) {
            return labels.zoom_out;
        }
        if (element.matches('.pnlm-fullscreen-toggle-button, [id^="fullscreen"], .fullscreen-button')) {
            return labels.fullscreen;
        }
        if (element.matches('.pnlm-orientation-button, [id^="gyroscope"]')) {
            return labels.orientation;
        }
        if (element.matches('[id^="pan-up"]')) {
            return labels.pan_up;
        }
        if (element.matches('[id^="pan-down"]')) {
            return labels.pan_down;
        }
        if (element.matches('[id^="pan-left"]')) {
            return labels.pan_left;
        }
        if (element.matches('[id^="pan-right"]')) {
            return labels.pan_right;
        }
        if (element.matches('[id^="backToHome"]')) {
            return labels.home;
        }
        if (element.matches('.audio_control')) {
            return labels.audio;
        }
        if (element.matches('.vrgcontrols')) {
            return labels.gallery;
        }
        if (element.matches('.explainer_button')) {
            return labels.explainer;
        }
        if (element.matches('.generic_form_button')) {
            return labels.form;
        }
        if (element.matches('.floor_map_button')) {
            return labels.floor_plan;
        }
        if (element.matches('.floor-plan-pointer')) {
            return labels.floor_plan_point;
        }
        if (element.matches('.custom-scene-navigation')) {
            return labels.scene_menu;
        }
        if (element.matches('.wpvr_owl_prev')) {
            return labels.previous_scene;
        }
        if (element.matches('.wpvr_owl_next')) {
            return labels.next_scene;
        }
        if (element.matches('.close-explainer-video, .close-generic-form, .close-floor-map-plan, .cross')) {
            return labels.close;
        }
        if (element.matches('.cp-logo-ctrl')) {
            return labels.company_info;
        }
        if (element.matches('.scctrl')) {
            var sceneTitle = element.closest('li');
            sceneTitle = sceneTitle ? sceneTitle.querySelector('.scene-title') : null;
            return sceneTitle && sceneTitle.textContent.trim()
                ? labels.scene + ': ' + sceneTitle.textContent.trim()
                : labels.scene;
        }
        if (element.matches('.scene-navigation-list')) {
            return labels.scene + ': ' + element.textContent.trim();
        }
        if (element.matches('.pnlm-hotspot-base, .pnlm-hotspot')) {
            var hotspotText = element.textContent.replace(/\s+/g, ' ').trim();
            return hotspotText ? hotspotText.slice(0, 160) : labels.hotspot;
        }

        var text = element.textContent.replace(/\s+/g, ' ').trim();
        return text ? text.slice(0, 160) : labels.hotspot;
    }

    function isNativeControl(element) {
        return /^(A|BUTTON|INPUT|SELECT|TEXTAREA)$/.test(element.tagName);
    }

    function prepareExternalHotspotLink(element) {
        if (!element.matches('.pnlm-hotspot-base, .pnlm-hotspot')) {
            return false;
        }

        var anchor = null;
        if (element.parentElement && element.parentElement.matches('a[href]')) {
            anchor = element.parentElement;
        } else {
            anchor = Array.prototype.find.call(element.children, function (child) {
                return child.matches && child.matches('a[href]');
            }) || null;
        }

        if (!anchor) {
            return externalHotspotLinks.has(element);
        }

        externalHotspotLinks.set(element, {
            url: anchor.href,
            target: anchor.getAttribute('target') || '_self'
        });

        anchor.removeAttribute('href');
        anchor.removeAttribute('target');
        anchor.setAttribute('tabindex', '-1');
        anchor.setAttribute('role', 'presentation');

        element.addEventListener('click', activateExternalHotspotLink);
        return true;
    }

    function activateExternalHotspotLink(event) {
        var link = externalHotspotLinks.get(event.currentTarget);
        if (!link || !link.url) {
            return;
        }

        event.preventDefault();

        var openInNewContext = link.target === '_blank' || event.ctrlKey || event.metaKey || event.shiftKey;
        if (openInNewContext) {
            var openedWindow = window.open(link.url, '_blank', 'noopener,noreferrer');
            if (openedWindow) {
                openedWindow.opener = null;
            }
        } else {
            window.location.assign(link.url);
        }
    }

    function enhanceControl(element) {
        if (enhancedControls.has(element)) {
            syncControlState(element);
            return;
        }

        if (
            element.matches('.ctrl') &&
            element.parentElement &&
            element.parentElement.matches('.explainer_button, .floor_map_button')
        ) {
            return;
        }

        enhancedControls.add(element);
        element.classList.add('wpvr-a11y-control');
        var isExternalHotspotLink = prepareExternalHotspotLink(element);
        if (!isNativeControl(element)) {
            element.setAttribute(
                'role',
                element.matches('.cp-logo-ctrl') ? 'group' : (isExternalHotspotLink ? 'link' : 'button')
            );
            element.setAttribute('tabindex', '0');
            if (!element.matches('.cp-logo-ctrl')) {
                element.addEventListener('keydown', activateControlFromKeyboard);
            }
        } else if (element.getAttribute('role') === 'presentation') {
            element.removeAttribute('role');
        }
        if (!element.getAttribute('aria-label')) {
            element.setAttribute('aria-label', labelFor(element));
        }

        associateControl(element);
        associateTooltip(element);
        syncControlState(element);
    }

    function activateControlFromKeyboard(event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.click();
    }

    function associateControl(element) {
        var tour = getTour(element);
        if (!tour || !tour.id) {
            return;
        }
        var suffix = tour.id.replace(/^pano/, '');
        var target = null;

        if (element.matches('.explainer_button')) {
            target = document.getElementById('explainer' + suffix);
        } else if (element.matches('.generic_form_button')) {
            target = document.getElementById('wpvr-generic-form' + suffix);
        } else if (element.matches('.floor_map_button')) {
            target = document.getElementById('wpvr-floor-map' + suffix);
        } else if (element.matches('.custom-scene-navigation')) {
            target = document.getElementById('custom-scene-navigation-nav' + suffix);
        } else if (element.matches('.vrgcontrols')) {
            target = document.getElementById('sccontrols' + suffix);
        }

        if (target && target.id) {
            element.setAttribute('aria-controls', target.id);
            element.setAttribute('aria-expanded', isVisible(target) ? 'true' : 'false');
        }
    }

    function associateTooltip(element) {
        if (!element.matches('.pnlm-hotspot-base, .pnlm-hotspot')) {
            return;
        }
        var tooltip = element.querySelector(':scope > p');
        if (!tooltip) {
            return;
        }
        if (!tooltip.id) {
            tooltipIndex += 1;
            tooltip.id = 'wpvr-hotspot-tooltip-' + tooltipIndex;
        }
        element.setAttribute('aria-describedby', tooltip.id);
    }

    function syncControlState(element) {
        if (element.matches('.audio_control')) {
            element.setAttribute('aria-pressed', element.getAttribute('data-play') === 'on' ? 'true' : 'false');
        }
        if (element.matches('.scene-navigation-list, .floor-plan-pointer')) {
            element.setAttribute(
                'aria-current',
                element.classList.contains('active') || element.classList.contains('add-pulse') ? 'true' : 'false'
            );
        }
        if (element.matches('.scctrl')) {
            var galleryItem = element.closest('.owl-item');
            element.setAttribute('aria-current', galleryItem && galleryItem.classList.contains('clicked') ? 'true' : 'false');
        }
        if (element.matches('.pnlm-fullscreen-toggle-button, [id^="fullscreen"], .fullscreen-button')) {
            var tour = getTour(element);
            var fullscreenElement = document.fullscreenElement || document.webkitFullscreenElement;
            element.setAttribute(
                'aria-pressed',
                fullscreenElement && (fullscreenElement === tour || fullscreenElement.contains(tour)) ? 'true' : 'false'
            );
        }
        if (element.matches('.pnlm-orientation-button, [id^="gyroscope"]')) {
            var orientationTour = getTour(element);
            var orientationViewer = getViewer(orientationTour);
            element.setAttribute(
                'aria-pressed',
                orientationViewer && typeof orientationViewer.isOrientationActive === 'function' &&
                    orientationViewer.isOrientationActive() ? 'true' : 'false'
            );
        }
        if (element.hasAttribute('aria-controls')) {
            var controlled = document.getElementById(element.getAttribute('aria-controls'));
            if (controlled) {
                element.setAttribute('aria-expanded', isVisible(controlled) ? 'true' : 'false');
            }
        }
    }

    function enhanceDialog(dialog) {
        if (!enhancedDialogs.has(dialog)) {
            enhancedDialogs.add(dialog);
            dialog.classList.add('wpvr-a11y-dialog');
            dialog.setAttribute('role', 'dialog');
            dialog.setAttribute('aria-modal', 'true');
            dialog.setAttribute('tabindex', '-1');
            if (!dialog.getAttribute('aria-label')) {
                dialog.setAttribute('aria-label', labels.dialog);
            }
            dialog.addEventListener('keydown', handleDialogKeydown);
        }
        syncDialog(dialog);
    }

    function syncDialog(dialog) {
        var visible = isVisible(dialog);
        var previous = dialogStates.get(dialog);
        dialog.setAttribute('aria-hidden', visible ? 'false' : 'true');
        dialogStates.set(dialog, visible);

        if (visible && previous !== true) {
            focusDialog(dialog);
        } else if (!visible && previous === true) {
            restoreDialogFocus(dialog);
        }
    }

    function focusDialog(dialog) {
        var tour = getTour(dialog);
        var opener = tour ? pendingOpeners.get(tour) : null;
        if (!opener || !document.documentElement.contains(opener)) {
            opener = tour;
        }
        dialog._wpvrOpener = opener;
        window.setTimeout(function () {
            var focusable = getFocusable(dialog);
            (focusable[0] || dialog).focus();
        }, 0);
    }

    function restoreDialogFocus(dialog) {
        var opener = dialog._wpvrOpener;
        if (opener && document.documentElement.contains(opener)) {
            opener.focus();
        }
        dialog._wpvrOpener = null;
    }

    function getFocusable(dialog) {
        return Array.prototype.filter.call(dialog.querySelectorAll(focusableSelector), function (element) {
            return isVisible(element) && element.getAttribute('aria-hidden') !== 'true';
        });
    }

    function handleDialogKeydown(event) {
        var dialog = event.currentTarget;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDialog(dialog);
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }

        var focusable = getFocusable(dialog);
        if (!focusable.length) {
            event.preventDefault();
            dialog.focus();
            return;
        }
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function closeDialog(dialog) {
        var close = dialog.querySelector('.close-explainer-video, .close-generic-form, .close-floor-map-plan, .cross');
        if (close) {
            close.click();
        } else {
            $(dialog).hide();
            syncDialog(dialog);
            return;
        }

        window.setTimeout(function () {
            if (isVisible(dialog)) {
                $(dialog).hide();
                $(dialog).closest('.pano-wrap').removeClass('show-modal');
            }
            syncDialog(dialog);
        }, 0);
    }

    function scan(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var tours = [];
        if (scope.matches && scope.matches('.pano-wrap.pnlm-container')) {
            tours.push(scope);
        }
        tours = tours.concat(Array.prototype.slice.call(scope.querySelectorAll('.pano-wrap.pnlm-container')));

        tours.forEach(function (tour) {
            enhanceContainer(tour);
            tour.querySelectorAll(controlSelector + ', .pnlm-hotspot-base, .pnlm-hotspot').forEach(enhanceControl);
            tour.querySelectorAll(dialogSelector).forEach(enhanceDialog);
        });
    }

    function queueScan(root) {
        if (scanQueued) {
            return;
        }
        scanQueued = true;
        window.requestAnimationFrame(function () {
            scanQueued = false;
            scan(root || document);
        });
    }

    document.addEventListener('click', function (event) {
        var opener = event.target.closest(controlSelector + ', .pnlm-hotspot-base, .pnlm-hotspot');
        var tour = getTour(event.target);
        if (opener && tour) {
            pendingOpeners.set(tour, opener);
        }
        if (event.target.closest('.pnlm-load-button') && tour) {
            focusTourAfterLoad(tour);
        }
        window.setTimeout(function () {
            if (tour) {
                scan(tour);
            }
        }, 0);
    }, true);

    document.addEventListener('wpvr:viewer-ready', function (event) {
        var container = event.detail && document.getElementById(event.detail.containerId);
        if (container) {
            scan(container);
        }
    });

    document.addEventListener('fullscreenchange', function () {
        queueScan(document);
    });

    $(function () {
        scan(document);
        var observer = new MutationObserver(function (mutations) {
            var shouldScan = mutations.some(function (mutation) {
                return mutation.type === 'childList' || mutation.attributeName === 'style' ||
                    mutation.attributeName === 'class' || mutation.attributeName === 'hidden' ||
                    mutation.attributeName === 'data-play';
            });
            if (shouldScan) {
                queueScan(document);
            }
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class', 'hidden', 'data-play']
        });
    });
})(jQuery);
