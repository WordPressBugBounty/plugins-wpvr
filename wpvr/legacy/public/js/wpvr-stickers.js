function wpvrFormatRgbaColor(color, opacityPercent) {
    if (!color) color = '#201b2c';
    var opacity = Number(opacityPercent !== undefined ? opacityPercent : 95) / 100;
    var clampedA = Math.max(0, Math.min(1, isNaN(opacity) ? 0.95 : opacity));
    if (typeof color === 'string' && color.indexOf('#') === 0) {
        var cleaned = color.replace('#', '').trim();
        if (cleaned.length === 3) {
            cleaned = cleaned.split('').map(function(c) { return c + c; }).join('');
        }
        if (cleaned.length === 6) {
            var r = parseInt(cleaned.slice(0, 2), 16) || 0;
            var g = parseInt(cleaned.slice(2, 4), 16) || 0;
            var b = parseInt(cleaned.slice(4, 6), 16) || 0;
            return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + Number(clampedA.toFixed(2)) + ')';
        }
    }
    var rgbMatch = typeof color === 'string' ? color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i) : null;
    if (rgbMatch) {
        return 'rgba(' + rgbMatch[1] + ', ' + rgbMatch[2] + ', ' + rgbMatch[3] + ', ' + Number(clampedA.toFixed(2)) + ')';
    }
    return color;
}

function wpvrFormatBorderRadius(rawRadius, fallback) {
    fallback = fallback !== undefined ? fallback : 20;
    if (typeof rawRadius === 'object' && rawRadius !== null) {
        var tl = Math.max(0, Number(rawRadius.topLeft !== undefined ? rawRadius.topLeft : fallback));
        var tr = Math.max(0, Number(rawRadius.topRight !== undefined ? rawRadius.topRight : fallback));
        var br = Math.max(0, Number(rawRadius.bottomRight !== undefined ? rawRadius.bottomRight : fallback));
        var bl = Math.max(0, Number(rawRadius.bottomLeft !== undefined ? rawRadius.bottomLeft : fallback));
        return tl + 'px ' + tr + 'px ' + br + 'px ' + bl + 'px';
    }
    if (typeof rawRadius === 'number' || (typeof rawRadius === 'string' && !isNaN(Number(rawRadius)))) {
        var r = Math.max(0, Number(rawRadius));
        return r + 'px';
    }
    return fallback + 'px';
}

function wpvrFormatPadding(rawPadding, fallback) {
    fallback = fallback !== undefined ? fallback : 21;
    if (typeof rawPadding === 'object' && rawPadding !== null) {
        var t = Math.max(0, Number(rawPadding.top !== undefined ? rawPadding.top : fallback));
        var r = Math.max(0, Number(rawPadding.right !== undefined ? rawPadding.right : fallback));
        var b = Math.max(0, Number(rawPadding.bottom !== undefined ? rawPadding.bottom : fallback));
        var l = Math.max(0, Number(rawPadding.left !== undefined ? rawPadding.left : fallback));
        return t + 'px ' + r + 'px ' + b + 'px ' + l + 'px';
    }
    if (typeof rawPadding === 'number' || (typeof rawPadding === 'string' && !isNaN(Number(rawPadding)))) {
        var p = Math.max(0, Number(rawPadding));
        return p + 'px';
    }
    return fallback + 'px';
}

function wpvrGetTourContainer(hotSpotDiv) {
    if (!hotSpotDiv) return null;
    return hotSpotDiv.closest('.pnlm-container') ||
           hotSpotDiv.closest('.pano-wrap') ||
           hotSpotDiv.closest('.wpvr-master-container') ||
           hotSpotDiv.closest('.wpvr-pannellum-container') ||
           hotSpotDiv.parentElement;
}

function wpvrGetTourWidth(container) {
    if (!container) return 1000;
    var width = container.clientWidth;
    if (!width || width <= 0) {
        if (container.getBoundingClientRect) {
            var rect = container.getBoundingClientRect();
            width = rect.width;
        }
    }
    if (!width || width <= 0) {
        if (container.parentElement && container.parentElement.clientWidth > 0) {
            width = container.parentElement.clientWidth;
        } else if (container.style && container.style.maxWidth && container.style.maxWidth.indexOf('px') !== -1) {
            width = parseFloat(container.style.maxWidth);
        } else if (container.style && container.style.width && container.style.width.indexOf('px') !== -1) {
            width = parseFloat(container.style.width);
        } else {
            width = window.innerWidth || 1000;
        }
    }
    return width;
}

function wpvrGetTourWidthRatio(container) {
    var width = wpvrGetTourWidth(container);
    var baseWidth = 1000;
    var ratio = width / baseWidth;
    return Math.max(0.3, Math.min(1.0, Math.round(ratio * 1000) / 1000));
}

function wpvrUpdateTourStickerScale(container) {
    if (!container) return 1;
    var ratio = wpvrGetTourWidthRatio(container);
    container.style.setProperty('--wpvr-sticker-scale', String(ratio));
    return ratio;
}

function wpvrObserveTourContainer(container) {
    if (!container || container._wpvrStickerObserverAttached) return;
    container._wpvrStickerObserverAttached = true;
    wpvrUpdateTourStickerScale(container);

    if (typeof ResizeObserver !== 'undefined') {
        var ro = new ResizeObserver(function(entries) {
            for (var i = 0; i < entries.length; i++) {
                var entry = entries[i];
                var width = entry.contentRect ? entry.contentRect.width : 0;
                if (!width && entry.target) {
                    width = entry.target.clientWidth;
                }
                if (width > 0) {
                    var ratio = Math.max(0.3, Math.min(1.0, Math.round((width / 1000) * 1000) / 1000));
                    container.style.setProperty('--wpvr-sticker-scale', String(ratio));
                }
            }
        });
        ro.observe(container);
    } else {
        window.addEventListener('resize', function() {
            wpvrUpdateTourStickerScale(container);
        });
    }
}

function wpvrAppendStickerCard(hotSpotDiv, card) {
    var container = wpvrGetTourContainer(hotSpotDiv);
    var ratio = 1;
    if (container) {
        wpvrObserveTourContainer(container);
        ratio = wpvrUpdateTourStickerScale(container);
    }

    var scaleWrapper = document.createElement('div');
    scaleWrapper.className = 'wpvr-sticker-scale-wrapper';
    scaleWrapper.style.transform = 'scale(var(--wpvr-sticker-scale, ' + ratio + '))';
    scaleWrapper.style.transformOrigin = 'center center';

    scaleWrapper.addEventListener('mousedown', function(e) { e.stopPropagation(); });
    scaleWrapper.addEventListener('touchstart', function(e) { e.stopPropagation(); });
    scaleWrapper.addEventListener('pointerdown', function(e) { e.stopPropagation(); });

    scaleWrapper.appendChild(card);
    hotSpotDiv.appendChild(scaleWrapper);

    if (!container || !container.clientWidth) {
        requestAnimationFrame(function() {
            var c = wpvrGetTourContainer(hotSpotDiv);
            if (c) {
                wpvrObserveTourContainer(c);
                var r = wpvrUpdateTourStickerScale(c);
                scaleWrapper.style.transform = 'scale(var(--wpvr-sticker-scale, ' + r + '))';
            }
        });
    }
}

function wpvrRenderStickerHotspot(hotSpotDiv, hs) {
    if (!hotSpotDiv || !hs) return;
    hotSpotDiv.classList.add('wpvr-hs--sticker');
    hotSpotDiv.style.overflow = 'visible';
    hotSpotDiv.style.cursor = 'pointer';
    hotSpotDiv.style.display = 'block';

    var existingWrapper = hotSpotDiv.querySelector('.wpvr-sticker-scale-wrapper');
    if (existingWrapper) {
        existingWrapper.parentNode.removeChild(existingWrapper);
    }
    var existingCard = hotSpotDiv.querySelector('.wpvr-sticker-card');
    if (existingCard) {
        existingCard.parentNode.removeChild(existingCard);
    }

    var template = hs.stickerTemplate || hs['hotspot-sticker-template'] || 'social_proof';
    var buttonBackground = hs.stickerBtnBg !== undefined ? hs.stickerBtnBg : hs['hotspot-sticker-btn-bg'];
    var buttonBackgroundOn = buttonBackground !== 'off' && buttonBackground !== false && buttonBackground !== 'false';
    if (template === 'social_proof') {
        var card = document.createElement('div');
        card.className = 'wpvr-sticker-card wpvr-sticker-card--social-proof';

        var rawBgColor = hs.stickerBgColor || hs['hotspot-sticker-bg-color'] || '#201b2c';
        var bgOpacity = hs.stickerBgOpacity !== undefined ? Number(hs.stickerBgOpacity) : (hs['hotspot-sticker-bg-opacity'] !== undefined ? Number(hs['hotspot-sticker-bg-opacity']) : 95);
        var blur = Math.max(0, Math.min(40, Number(hs.stickerBlur !== undefined ? hs.stickerBlur : (hs['hotspot-sticker-blur'] !== undefined ? hs['hotspot-sticker-blur'] : 12))));
        var brightness = Math.max(0, Math.min(200, Number(hs.stickerBrightness !== undefined ? hs.stickerBrightness : (hs['hotspot-sticker-brightness'] !== undefined ? hs['hotspot-sticker-brightness'] : 100))));
        var brightnessRatio = Number((brightness / 100).toFixed(2));
        var filterValue = 'blur(' + blur + 'px) brightness(' + brightnessRatio + ')';

        var rawBorderColor = hs.stickerBorderColor !== undefined ? hs.stickerBorderColor : (hs['hotspot-sticker-border-color'] !== undefined ? hs['hotspot-sticker-border-color'] : '#3a3051');
        var textColor = hs.stickerTextColor || hs['hotspot-sticker-text-color'] || '#ffffff';
        var starColor = hs.stickerStarColor || hs['hotspot-sticker-star-color'] || '#EF991F';
        var rating = Math.max(1, Math.min(5, Number(hs.stickerRating || hs['hotspot-sticker-rating']) || 5));
        var reviewText = hs.stickerReviewText !== undefined ? hs.stickerReviewText : (hs['hotspot-sticker-review-text'] !== undefined ? hs['hotspot-sticker-review-text'] : 'The support is super responsive and responds without worries to our requests and needs! Big up to the entire RexTheme team!');
        var clientName = hs.stickerClientName !== undefined ? hs.stickerClientName : (hs['hotspot-sticker-client-name'] !== undefined ? hs['hotspot-sticker-client-name'] : 'Elena R.');
        var avatarUrl = hs.stickerClientAvatar || hs['hotspot-sticker-client-avatar'] || '';
        var borderRadius = hs.stickerBorderRadius !== undefined ? hs.stickerBorderRadius : hs['hotspot-sticker-border-radius'];

        card.style.backgroundColor = wpvrFormatRgbaColor(rawBgColor, bgOpacity);
        card.style.backdropFilter = filterValue;
        card.style.webkitBackdropFilter = filterValue;

        if (rawBorderColor === 'none' || rawBorderColor === 'transparent') {
            card.style.border = 'none';
        } else {
            card.style.border = '1px solid ' + rawBorderColor;
        }
        card.style.borderRadius = wpvrFormatBorderRadius(borderRadius, 20);
        card.style.color = textColor;
        card.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');

        // Rating stars row
        var starsRow = document.createElement('div');
        starsRow.className = 'wpvr-sticker-stars';
        for (var i = 1; i <= 5; i++) {
            var starSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            starSvg.setAttribute('width', '16');
            starSvg.setAttribute('height', '15');
            starSvg.setAttribute('viewBox', '0 0 16 15');
            starSvg.setAttribute('fill', 'none');
            starSvg.setAttribute('aria-hidden', 'true');
            var fillColor = i <= rating ? starColor : 'rgba(255, 255, 255, 0.2)';
            starSvg.innerHTML = '<path d="M10.4717 4.42003L14.6575 5.03473C15.0088 5.09327 15.3015 5.32745 15.4186 5.67871C15.5357 6.0007 15.4479 6.38123 15.1844 6.6154L12.1401 9.63039L12.8719 13.9041C12.9305 14.2553 12.7841 14.6066 12.4914 14.8115C12.1987 15.0456 11.8182 15.0456 11.4962 14.8993L7.74937 12.8795L3.97331 14.8993C3.68059 15.0456 3.27079 15.0456 3.00734 14.8115C2.71462 14.6066 2.56826 14.2553 2.62681 13.9041L3.32933 9.63039L0.285059 6.6154C0.021613 6.38123 -0.0662025 6.0007 0.0508848 5.67871C0.167972 5.32745 0.46069 5.09327 0.811952 5.03473L5.02709 4.42003L6.90049 0.52689C7.04685 0.204902 7.36884 0 7.74937 0C8.10064 0 8.42263 0.204902 8.56898 0.52689L10.4717 4.42003Z" fill="' + fillColor + '"/>';
            starsRow.appendChild(starSvg);
        }
        card.appendChild(starsRow);

        // Review text
        var reviewEl = document.createElement('p');
        reviewEl.className = 'wpvr-sticker-review';
        reviewEl.textContent = reviewText;
        reviewEl.style.color = textColor;
        reviewEl.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        card.appendChild(reviewEl);

        // Client row
        var clientRow = document.createElement('div');
        clientRow.className = 'wpvr-sticker-client';

        var avatarWrap = document.createElement('div');
        avatarWrap.className = 'wpvr-sticker-avatar';
        if (avatarUrl) {
            var img = document.createElement('img');
            img.src = avatarUrl;
            img.alt = clientName;
            avatarWrap.appendChild(img);
        } else {
            avatarWrap.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="opacity: 0.75;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
        }
        clientRow.appendChild(avatarWrap);

        var nameEl = document.createElement('span');
        nameEl.className = 'wpvr-sticker-name';
        nameEl.textContent = clientName;
        nameEl.style.color = textColor;
        nameEl.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        clientRow.appendChild(nameEl);

        card.appendChild(clientRow);
        wpvrAppendStickerCard(hotSpotDiv, card);
    } else if (template === 'discount_button') {
        var card = document.createElement('div');
        card.className = 'wpvr-sticker-card wpvr-sticker-card--discount-btn';

        var rawBgColor = hs.stickerBgColor || hs['hotspot-sticker-bg-color'] || '#201b2c';
        var bgOpacity = hs.stickerBgOpacity !== undefined ? Number(hs.stickerBgOpacity) : (hs['hotspot-sticker-bg-opacity'] !== undefined ? Number(hs['hotspot-sticker-bg-opacity']) : 75);
        var blur = Math.max(0, Math.min(40, Number(hs.stickerBlur !== undefined ? hs.stickerBlur : (hs['hotspot-sticker-blur'] !== undefined ? hs['hotspot-sticker-blur'] : 12))));
        var brightness = Math.max(0, Math.min(200, Number(hs.stickerBrightness !== undefined ? hs.stickerBrightness : (hs['hotspot-sticker-brightness'] !== undefined ? hs['hotspot-sticker-brightness'] : 100))));
        var brightnessRatio = Number((brightness / 100).toFixed(2));
        var filterValue = 'blur(' + blur + 'px) brightness(' + brightnessRatio + ')';

        var rawBorderColor = hs.stickerBorderColor !== undefined ? hs.stickerBorderColor : (hs['hotspot-sticker-border-color'] !== undefined ? hs['hotspot-sticker-border-color'] : '#40355a');
        var btnColor = hs.stickerBtnColor || hs['hotspot-sticker-btn-color'] || '#EF991F';
        var btnTextColor = hs.stickerBtnTextColor || hs['hotspot-sticker-btn-text-color'] || '#000000';
        var btnIconColor = hs.stickerBtnIconColor || hs['hotspot-sticker-btn-icon-color'] || '#000000';
        var btnText = hs.stickerBtnText !== undefined ? hs.stickerBtnText : (hs['hotspot-sticker-btn-text'] !== undefined ? hs['hotspot-sticker-btn-text'] : 'GET 20% OFF NOW');
        var btnUrl = hs.stickerBtnUrl || hs['hotspot-sticker-btn-url'] || '';
        var btnNewTab = hs.stickerBtnNewTab || hs['hotspot-sticker-btn-new-tab'] || 'off';
        var borderRadius = hs.stickerBorderRadius !== undefined ? hs.stickerBorderRadius : hs['hotspot-sticker-border-radius'];

        var mainBg = hs.stickerMainBg !== undefined ? hs.stickerMainBg : (hs['hotspot-sticker-main-bg'] !== undefined ? hs['hotspot-sticker-main-bg'] : (hs.stickerCardBg !== undefined ? hs.stickerCardBg : (hs['hotspot-sticker-card-bg'] !== undefined ? hs['hotspot-sticker-card-bg'] : 'on')));
        var mainBgOn = mainBg !== 'off' && mainBg !== false && mainBg !== 'false';

        var rawWidth = hs.stickerBtnWidth !== undefined ? hs.stickerBtnWidth : hs['hotspot-sticker-btn-width'];
        var btnWidth = rawWidth !== undefined && rawWidth !== '' && !isNaN(Number(rawWidth)) && Number(rawWidth) > 0 ? Number(rawWidth) : null;

        var rawHeight = hs.stickerBtnHeight !== undefined ? hs.stickerBtnHeight : hs['hotspot-sticker-btn-height'];
        var btnHeight = rawHeight !== undefined && rawHeight !== '' && !isNaN(Number(rawHeight)) && Number(rawHeight) > 0 ? Number(rawHeight) : null;

        var rawRadius = hs.stickerBtnRadius !== undefined ? hs.stickerBtnRadius : hs['hotspot-sticker-btn-radius'];
        var btnRadius = rawRadius !== undefined && rawRadius !== '' && !isNaN(Number(rawRadius)) ? Math.max(0, Number(rawRadius)) : 10;

        var rawBorder = hs.stickerBtnBorder !== undefined ? hs.stickerBtnBorder : hs['hotspot-sticker-btn-border'];
        var btnBorder = rawBorder !== undefined && rawBorder !== '' && !isNaN(Number(rawBorder)) ? Math.max(0, Number(rawBorder)) : 0;

        var btnBorderColor = hs.stickerBtnBorderColor || hs['hotspot-sticker-btn-border-color'] || '#EF991F';

        var rawTextSize = hs.stickerBtnTextSize !== undefined ? hs.stickerBtnTextSize : hs['hotspot-sticker-btn-text-size'];
        var btnTextSize = rawTextSize !== undefined && rawTextSize !== '' && !isNaN(Number(rawTextSize)) ? Math.max(8, Number(rawTextSize)) : 18;

        var btnTextWeight = String(hs.stickerBtnTextWeight || hs['hotspot-sticker-btn-text-weight'] || '700');
        var btnIcon = hs.stickerBtnIcon !== undefined ? hs.stickerBtnIcon : (hs['hotspot-sticker-btn-icon'] !== undefined ? hs['hotspot-sticker-btn-icon'] : (hs.iconClass || 'fas fa-tag'));

        if (mainBgOn) {
            card.style.backgroundColor = wpvrFormatRgbaColor(rawBgColor, bgOpacity);
            card.style.backdropFilter = filterValue;
            card.style.webkitBackdropFilter = filterValue;

            if (rawBorderColor === 'none' || rawBorderColor === 'transparent') {
                card.style.border = 'none';
            } else {
                card.style.border = '1px solid ' + rawBorderColor;
            }
            card.style.borderRadius = wpvrFormatBorderRadius(borderRadius, 15);
            card.style.boxShadow = '0 12px 32px rgba(0, 0, 0, 0.45)';
            var padding = hs.stickerPadding !== undefined ? hs.stickerPadding : hs['hotspot-sticker-padding'];
            card.style.padding = wpvrFormatPadding(padding, 21);
        } else {
            card.classList.add('wpvr-sticker-card--no-main-bg');
            card.style.backgroundColor = 'transparent';
            card.style.backdropFilter = 'none';
            card.style.webkitBackdropFilter = 'none';
            card.style.border = 'none';
            card.style.boxShadow = 'none';
            card.style.padding = '0';
        }
        card.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');

        if (btnWidth || !mainBgOn) {
            card.style.width = 'auto';
            card.style.maxWidth = 'none';
        }

        // Inner button (use <a> if link is provided)
        var btnEl;
        if (btnUrl) {
            btnEl = document.createElement('a');
            btnEl.href = btnUrl;
            btnEl.className = 'wpvr-sticker-discount-btn';
            if (btnNewTab === 'on' || btnNewTab === true || btnNewTab === 'true') {
                btnEl.target = '_blank';
                btnEl.rel = 'noopener noreferrer';
            }
        } else {
            btnEl = document.createElement('div');
            btnEl.className = 'wpvr-sticker-discount-btn';
        }
        btnEl.style.backgroundColor = buttonBackgroundOn ? btnColor : 'transparent';
        btnEl.style.boxShadow = buttonBackgroundOn ? '0px 10px 30px rgba(0, 0, 0, 0.25)' : 'none';
        btnEl.style.borderRadius = btnRadius + 'px';
        btnEl.style.display = 'flex';
        btnEl.style.alignItems = 'center';
        btnEl.style.justifyContent = 'center';
        btnEl.style.gap = '10px';
        btnEl.style.padding = '16px 22px';
        btnEl.style.cursor = 'pointer';
        btnEl.style.textDecoration = 'none';
        btnEl.style.boxSizing = 'border-box';

        if (btnWidth) {
            btnEl.style.width = btnWidth + 'px';
        } else if (!mainBgOn) {
            btnEl.style.width = 'auto';
        } else {
            btnEl.style.width = '100%';
        }
        if (btnHeight) {
            btnEl.style.height = btnHeight + 'px';
            btnEl.style.padding = '0 20px';
        }

        if (btnBorder > 0 && btnBorderColor !== 'none' && btnBorderColor !== 'transparent') {
            btnEl.style.border = btnBorder + 'px solid ' + btnBorderColor;
        } else {
            btnEl.style.border = 'none';
        }

        var textEl = document.createElement('span');
        textEl.className = 'wpvr-sticker-discount-text';
        textEl.textContent = btnText;
        textEl.style.color = btnTextColor;
        textEl.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        textEl.style.fontWeight = btnTextWeight;
        textEl.style.fontSize = btnTextSize + 'px';
        textEl.style.lineHeight = Math.round(btnTextSize * 1.33) + 'px';
        textEl.style.whiteSpace = 'nowrap';
        btnEl.appendChild(textEl);

        // Icon selection
        if (btnIcon && btnIcon !== 'none') {
            var iconEl = document.createElement('i');
            iconEl.className = btnIcon;
            iconEl.style.color = btnIconColor;
            iconEl.style.fontSize = btnTextSize + 'px';
            iconEl.style.lineHeight = '1';
            iconEl.style.flexShrink = '0';
            iconEl.style.display = 'inline-flex';
            iconEl.style.alignItems = 'center';
            iconEl.style.justifyContent = 'center';
            if (btnIcon.indexOf('fab') !== -1) {
                iconEl.style.setProperty('font-family', '"Font Awesome 6 Brands", "Font Awesome 5 Brands"', 'important');
                iconEl.style.setProperty('font-weight', '400', 'important');
            } else if (btnIcon.indexOf('far') !== -1) {
                iconEl.style.setProperty('font-family', '"Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome"', 'important');
                iconEl.style.setProperty('font-weight', '400', 'important');
            } else {
                iconEl.style.setProperty('font-family', '"Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome"', 'important');
                iconEl.style.setProperty('font-weight', '900', 'important');
            }
            iconEl.style.setProperty('font-style', 'normal', 'important');
            btnEl.appendChild(iconEl);
        }

        btnEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        btnEl.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        btnEl.addEventListener('pointerdown', function(e) { e.stopPropagation(); });
        if (btnUrl) {
            btnEl.addEventListener('click', function(e) { e.stopPropagation(); });
        }
        card.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        card.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        card.addEventListener('pointerdown', function(e) { e.stopPropagation(); });

        card.appendChild(btnEl);
        wpvrAppendStickerCard(hotSpotDiv, card);
    } else if (template === 'button_with_separate_icon') {
        var card = document.createElement('div');
        card.className = 'wpvr-sticker-card wpvr-sticker-card--btn-separate-icon';

        var rawBgColor = hs.stickerBgColor || hs['hotspot-sticker-bg-color'] || '#201b2c';
        var bgOpacity = hs.stickerBgOpacity !== undefined ? Number(hs.stickerBgOpacity) : (hs['hotspot-sticker-bg-opacity'] !== undefined ? Number(hs['hotspot-sticker-bg-opacity']) : 75);
        var blur = Math.max(0, Math.min(40, Number(hs.stickerBlur !== undefined ? hs.stickerBlur : (hs['hotspot-sticker-blur'] !== undefined ? hs['hotspot-sticker-blur'] : 12))));
        var brightness = Math.max(0, Math.min(200, Number(hs.stickerBrightness !== undefined ? hs.stickerBrightness : (hs['hotspot-sticker-brightness'] !== undefined ? hs['hotspot-sticker-brightness'] : 100))));
        var brightnessRatio = Number((brightness / 100).toFixed(2));
        var filterValue = 'blur(' + blur + 'px) brightness(' + brightnessRatio + ')';

        var rawBorderColor = hs.stickerBorderColor !== undefined ? hs.stickerBorderColor : (hs['hotspot-sticker-border-color'] !== undefined ? hs['hotspot-sticker-border-color'] : '#40355a');
        var btnColor = hs.stickerBtnColor || hs['hotspot-sticker-btn-color'] || '#ffffff';
        var btnTextColor = hs.stickerBtnTextColor || hs['hotspot-sticker-btn-text-color'] || '#000000';
        var btnIconColor = hs.stickerBtnIconColor || hs['hotspot-sticker-btn-icon-color'] || '#000000';
        var btnText = hs.stickerBtnText !== undefined ? hs.stickerBtnText : (hs['hotspot-sticker-btn-text'] !== undefined ? hs['hotspot-sticker-btn-text'] : 'Limited “Midnight Horizon” Edition');
        var btnUrl = hs.stickerBtnUrl || hs['hotspot-sticker-btn-url'] || '';
        var btnNewTab = hs.stickerBtnNewTab || hs['hotspot-sticker-btn-new-tab'] || 'off';
        var borderRadius = hs.stickerBorderRadius !== undefined ? hs.stickerBorderRadius : hs['hotspot-sticker-border-radius'];

        var mainBg = hs.stickerMainBg !== undefined ? hs.stickerMainBg : (hs['hotspot-sticker-main-bg'] !== undefined ? hs['hotspot-sticker-main-bg'] : (hs.stickerCardBg !== undefined ? hs.stickerCardBg : (hs['hotspot-sticker-card-bg'] !== undefined ? hs['hotspot-sticker-card-bg'] : 'on')));
        var mainBgOn = mainBg !== 'off' && mainBg !== false && mainBg !== 'false';

        var rawWidth = hs.stickerBtnWidth !== undefined ? hs.stickerBtnWidth : hs['hotspot-sticker-btn-width'];
        var btnWidth = rawWidth !== undefined && rawWidth !== '' && !isNaN(Number(rawWidth)) && Number(rawWidth) > 0 ? Number(rawWidth) : 356;

        var rawHeight = hs.stickerBtnHeight !== undefined ? hs.stickerBtnHeight : hs['hotspot-sticker-btn-height'];
        var btnHeight = rawHeight !== undefined && rawHeight !== '' && !isNaN(Number(rawHeight)) && Number(rawHeight) > 0 ? Number(rawHeight) : 60;

        var rawRadius = hs.stickerBtnRadius !== undefined ? hs.stickerBtnRadius : hs['hotspot-sticker-btn-radius'];
        var btnRadius = rawRadius !== undefined && rawRadius !== '' && !isNaN(Number(rawRadius)) ? Math.max(0, Number(rawRadius)) : 30;

        var rawBorder = hs.stickerBtnBorder !== undefined ? hs.stickerBtnBorder : hs['hotspot-sticker-btn-border'];
        var btnBorder = rawBorder !== undefined && rawBorder !== '' && !isNaN(Number(rawBorder)) ? Math.max(0, Number(rawBorder)) : 0;

        var btnBorderColor = hs.stickerBtnBorderColor || hs['hotspot-sticker-btn-border-color'] || '#ffffff';

        var rawTextSize = hs.stickerBtnTextSize !== undefined ? hs.stickerBtnTextSize : hs['hotspot-sticker-btn-text-size'];
        var btnTextSize = rawTextSize !== undefined && rawTextSize !== '' && !isNaN(Number(rawTextSize)) ? Math.max(8, Number(rawTextSize)) : 18;

        var btnTextWeight = String(hs.stickerBtnTextWeight || hs['hotspot-sticker-btn-text-weight'] || '600');
        var btnIcon = hs.stickerBtnIcon !== undefined ? hs.stickerBtnIcon : (hs['hotspot-sticker-btn-icon'] !== undefined ? hs['hotspot-sticker-btn-icon'] : (hs.iconClass || 'fas fa-tag'));

        // Separate Icon Box configuration
        var iconBoxBgColor = hs.stickerIconBoxBgColor || hs['hotspot-sticker-icon-box-bg-color'] || '#ffffff';
        var rawIconBoxSize = hs.stickerIconBoxSize !== undefined ? hs.stickerIconBoxSize : hs['hotspot-sticker-icon-box-size'];
        var iconBoxSize = rawIconBoxSize !== undefined && rawIconBoxSize !== '' && !isNaN(Number(rawIconBoxSize)) ? Math.max(20, Number(rawIconBoxSize)) : 60;

        var rawIconBoxRadius = hs.stickerIconBoxRadius !== undefined ? hs.stickerIconBoxRadius : hs['hotspot-sticker-icon-box-radius'];
        var iconBoxRadius = rawIconBoxRadius !== undefined && rawIconBoxRadius !== '' && !isNaN(Number(rawIconBoxRadius)) ? Math.max(0, Number(rawIconBoxRadius)) : 30;

        var rawIconBoxBorder = hs.stickerIconBoxBorder !== undefined ? hs.stickerIconBoxBorder : hs['hotspot-sticker-icon-box-border'];
        var iconBoxBorder = rawIconBoxBorder !== undefined && rawIconBoxBorder !== '' && !isNaN(Number(rawIconBoxBorder)) ? Math.max(0, Number(rawIconBoxBorder)) : 0;

        var iconBoxBorderColor = hs.stickerIconBoxBorderColor || hs['hotspot-sticker-icon-box-border-color'] || '#ffffff';

        if (mainBgOn) {
            card.style.backgroundColor = wpvrFormatRgbaColor(rawBgColor, bgOpacity);
            card.style.backdropFilter = filterValue;
            card.style.webkitBackdropFilter = filterValue;

            if (rawBorderColor === 'none' || rawBorderColor === 'transparent') {
                card.style.border = 'none';
            } else {
                card.style.border = '1px solid ' + rawBorderColor;
            }
            card.style.borderRadius = wpvrFormatBorderRadius(borderRadius, 135);
            card.style.boxShadow = '0 12px 32px rgba(0, 0, 0, 0.45)';
            var padding = hs.stickerPadding !== undefined ? hs.stickerPadding : hs['hotspot-sticker-padding'];
            card.style.padding = wpvrFormatPadding(padding, 21);
        } else {
            card.classList.add('wpvr-sticker-card--no-main-bg');
            card.style.backgroundColor = 'transparent';
            card.style.backdropFilter = 'none';
            card.style.webkitBackdropFilter = 'none';
            card.style.border = 'none';
            card.style.boxShadow = 'none';
            card.style.padding = '0';
        }
        card.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        card.style.width = 'auto';
        card.style.maxWidth = 'none';

        // Outer cumulative button (use <a> if url is present)
        var wrapEl;
        if (btnUrl) {
            wrapEl = document.createElement('a');
            wrapEl.href = btnUrl;
            wrapEl.className = 'wpvr-sticker-btn-separate-wrap';
            if (btnNewTab === 'on' || btnNewTab === true || btnNewTab === 'true') {
                wrapEl.target = '_blank';
                wrapEl.rel = 'noopener noreferrer';
            }
        } else {
            wrapEl = document.createElement('div');
            wrapEl.className = 'wpvr-sticker-btn-separate-wrap';
        }
        wrapEl.style.display = 'inline-flex';
        wrapEl.style.alignItems = 'center';
        wrapEl.style.gap = '15px';
        wrapEl.style.cursor = 'pointer';
        wrapEl.style.textDecoration = 'none';

        // 1. Text button div
        var textBtnDiv = document.createElement('div');
        textBtnDiv.className = 'wpvr-sticker-separate-btn-text';
        textBtnDiv.style.backgroundColor = buttonBackgroundOn ? btnColor : 'transparent';
        textBtnDiv.style.boxShadow = buttonBackgroundOn ? '0px 10px 30px rgba(0, 0, 0, 0.25)' : 'none';
        textBtnDiv.style.borderRadius = btnRadius + 'px';
        textBtnDiv.style.display = 'flex';
        textBtnDiv.style.alignItems = 'center';
        textBtnDiv.style.justifyContent = 'center';
        textBtnDiv.style.padding = '0 28px';
        textBtnDiv.style.height = btnHeight + 'px';
        textBtnDiv.style.boxSizing = 'border-box';
        if (btnWidth) {
            textBtnDiv.style.width = btnWidth + 'px';
        } else {
            textBtnDiv.style.width = 'auto';
        }
        if (btnBorder > 0 && btnBorderColor !== 'none' && btnBorderColor !== 'transparent') {
            textBtnDiv.style.border = btnBorder + 'px solid ' + btnBorderColor;
        } else {
            textBtnDiv.style.border = 'none';
        }

        var textEl = document.createElement('span');
        textEl.textContent = btnText;
        textEl.style.color = btnTextColor;
        textEl.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        textEl.style.fontWeight = btnTextWeight;
        textEl.style.fontSize = btnTextSize + 'px';
        textEl.style.lineHeight = '34px';
        textEl.style.whiteSpace = 'nowrap';
        textBtnDiv.appendChild(textEl);

        wrapEl.appendChild(textBtnDiv);

        // 2. Separate icon box container
        if (btnIcon && btnIcon !== 'none') {
            var iconBoxDiv = document.createElement('div');
            iconBoxDiv.className = 'wpvr-sticker-separate-icon-box';
            iconBoxDiv.style.backgroundColor = iconBoxBgColor;
            iconBoxDiv.style.boxShadow = '0px 10px 30px rgba(0, 0, 0, 0.25)';
            iconBoxDiv.style.borderRadius = iconBoxRadius + 'px';
            iconBoxDiv.style.width = iconBoxSize + 'px';
            iconBoxDiv.style.height = iconBoxSize + 'px';
            iconBoxDiv.style.display = 'flex';
            iconBoxDiv.style.alignItems = 'center';
            iconBoxDiv.style.justifyContent = 'center';
            iconBoxDiv.style.flexShrink = '0';
            iconBoxDiv.style.boxSizing = 'border-box';

            if (iconBoxBorder > 0 && iconBoxBorderColor !== 'none' && iconBoxBorderColor !== 'transparent') {
                iconBoxDiv.style.border = iconBoxBorder + 'px solid ' + iconBoxBorderColor;
            } else {
                iconBoxDiv.style.border = 'none';
            }

            if (!btnIcon || btnIcon === 'fas fa-tag' || btnIcon === 'far fa-tag' || btnIcon === 'fa-tag' || btnIcon === 'tag-2') {
                var svgWrap = document.createElement('span');
                svgWrap.style.display = 'inline-flex';
                svgWrap.style.alignItems = 'center';
                svgWrap.style.justifyContent = 'center';
                svgWrap.style.color = btnIconColor;
                svgWrap.innerHTML = '<svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display:block;flex-shrink:0;"><path d="M2.68758 18.0276L8.93582 24.2759C11.5013 26.8414 15.6668 26.8414 18.2461 24.2759L24.3012 18.2207C26.8667 15.6552 26.8667 11.4898 24.3012 8.91046L18.0392 2.67602C16.7289 1.36568 14.922 0.662235 13.0737 0.758786L6.17722 1.08982C3.41861 1.21396 1.22552 3.40705 1.08759 6.15186L0.756561 13.0484C0.673803 14.9104 1.37725 16.7173 2.68758 18.0276Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.0393 13.4759C11.9437 13.4759 13.4875 11.9321 13.4875 10.0277C13.4875 8.12324 11.9437 6.57941 10.0393 6.57941C8.13485 6.57941 6.59101 8.12324 6.59101 10.0277C6.59101 11.9321 8.13485 13.4759 10.0393 13.4759Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M14.8668 20.3724L20.384 14.8552" stroke="currentColor" stroke-width="1.8" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                iconBoxDiv.appendChild(svgWrap);
            } else {
                var iconEl = document.createElement('i');
                iconEl.className = btnIcon;
                iconEl.style.color = btnIconColor;
                iconEl.style.fontSize = Math.round(iconBoxSize * 0.35) + 'px';
                iconEl.style.lineHeight = '1';
                iconEl.style.display = 'inline-flex';
                iconEl.style.alignItems = 'center';
                iconEl.style.justifyContent = 'center';
                if (btnIcon.indexOf('fab') !== -1) {
                    iconEl.style.setProperty('font-family', '"Font Awesome 6 Brands", "Font Awesome 5 Brands"', 'important');
                    iconEl.style.setProperty('font-weight', '400', 'important');
                } else if (btnIcon.indexOf('far') !== -1) {
                    iconEl.style.setProperty('font-family', '"Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome"', 'important');
                    iconEl.style.setProperty('font-weight', '400', 'important');
                } else {
                    iconEl.style.setProperty('font-family', '"Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome"', 'important');
                    iconEl.style.setProperty('font-weight', '900', 'important');
                }
                iconEl.style.setProperty('font-style', 'normal', 'important');
                iconBoxDiv.appendChild(iconEl);
            }

            wrapEl.appendChild(iconBoxDiv);
        }

        wrapEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        wrapEl.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        wrapEl.addEventListener('pointerdown', function(e) { e.stopPropagation(); });
        if (btnUrl) {
            wrapEl.addEventListener('click', function(e) { e.stopPropagation(); });
        }
        card.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        card.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        card.addEventListener('pointerdown', function(e) { e.stopPropagation(); });

        card.appendChild(wrapEl);
        wpvrAppendStickerCard(hotSpotDiv, card);
    } else if (template === 'add_to_cart') {
        var card = document.createElement('div');
        card.className = 'wpvr-sticker-card wpvr-sticker-card--add-to-cart';

        var rawBgColor = hs.stickerBgColor || hs['hotspot-sticker-bg-color'] || '#201b2c';
        var bgOpacity = hs.stickerBgOpacity !== undefined ? Number(hs.stickerBgOpacity) : (hs['hotspot-sticker-bg-opacity'] !== undefined ? Number(hs['hotspot-sticker-bg-opacity']) : 75);
        var blur = Math.max(0, Math.min(40, Number(hs.stickerBlur !== undefined ? hs.stickerBlur : (hs['hotspot-sticker-blur'] !== undefined ? hs['hotspot-sticker-blur'] : 12))));
        var brightness = Math.max(0, Math.min(200, Number(hs.stickerBrightness !== undefined ? hs.stickerBrightness : (hs['hotspot-sticker-brightness'] !== undefined ? hs['hotspot-sticker-brightness'] : 100))));
        var brightnessRatio = Number((brightness / 100).toFixed(2));
        var filterValue = 'blur(' + blur + 'px) brightness(' + brightnessRatio + ')';

        var rawBorderColor = hs.stickerBorderColor !== undefined ? hs.stickerBorderColor : (hs['hotspot-sticker-border-color'] !== undefined ? hs['hotspot-sticker-border-color'] : '#40355a');
        var btnColor = hs.stickerBtnColor || hs['hotspot-sticker-btn-color'] || '#3f04fe';
        var btnTextColor = hs.stickerBtnTextColor || hs['hotspot-sticker-btn-text-color'] || '#ffffff';
        var btnText = hs.stickerBtnText !== undefined ? hs.stickerBtnText : (hs['hotspot-sticker-btn-text'] !== undefined ? hs['hotspot-sticker-btn-text'] : 'ADD TO CART');
        var btnUrl = hs.stickerBtnUrl || hs['hotspot-sticker-btn-url'] || '';
        var btnNewTab = hs.stickerBtnNewTab || hs['hotspot-sticker-btn-new-tab'] || 'off';
        var textColor = hs.stickerTextColor || hs['hotspot-sticker-text-color'] || '#ffffff';
        var prefixText = hs.stickerPrefixText !== undefined ? hs.stickerPrefixText : (hs['hotspot-sticker-prefix-text'] !== undefined ? hs['hotspot-sticker-prefix-text'] : '$1,090 -');
        var subText = hs.stickerSubText !== undefined ? hs.stickerSubText : (hs['hotspot-sticker-sub-text'] !== undefined ? hs['hotspot-sticker-sub-text'] : 'Ready to ship');

        var borderRadius = hs.stickerBorderRadius !== undefined ? hs.stickerBorderRadius : hs['hotspot-sticker-border-radius'];
        var padding = hs.stickerPadding !== undefined ? hs.stickerPadding : hs['hotspot-sticker-padding'];

        var mainBg = hs.stickerMainBg !== undefined ? hs.stickerMainBg : (hs['hotspot-sticker-main-bg'] !== undefined ? hs['hotspot-sticker-main-bg'] : (hs.stickerCardBg !== undefined ? hs.stickerCardBg : (hs['hotspot-sticker-card-bg'] !== undefined ? hs['hotspot-sticker-card-bg'] : 'on')));
        var mainBgOn = mainBg !== 'off' && mainBg !== false && mainBg !== 'false';

        var rawWidth = hs.stickerBtnWidth !== undefined ? hs.stickerBtnWidth : hs['hotspot-sticker-btn-width'];
        var btnWidth = rawWidth !== undefined && rawWidth !== '' && !isNaN(Number(rawWidth)) && Number(rawWidth) > 0 ? Number(rawWidth) : 163;

        var rawHeight = hs.stickerBtnHeight !== undefined ? hs.stickerBtnHeight : hs['hotspot-sticker-btn-height'];
        var btnHeight = rawHeight !== undefined && rawHeight !== '' && !isNaN(Number(rawHeight)) && Number(rawHeight) > 0 ? Number(rawHeight) : 60;

        var rawRadius = hs.stickerBtnRadius !== undefined ? hs.stickerBtnRadius : hs['hotspot-sticker-btn-radius'];
        var btnRadius = rawRadius !== undefined && rawRadius !== '' && !isNaN(Number(rawRadius)) ? Math.max(0, Number(rawRadius)) : 10;

        var rawBorder = hs.stickerBtnBorder !== undefined ? hs.stickerBtnBorder : hs['hotspot-sticker-btn-border'];
        var btnBorder = rawBorder !== undefined && rawBorder !== '' && !isNaN(Number(rawBorder)) ? Math.max(0, Number(rawBorder)) : 0;

        var btnBorderColor = hs.stickerBtnBorderColor || hs['hotspot-sticker-btn-border-color'] || '#ffffff';

        var rawTextSize = hs.stickerBtnTextSize !== undefined ? hs.stickerBtnTextSize : hs['hotspot-sticker-btn-text-size'];
        var btnTextSize = rawTextSize !== undefined && rawTextSize !== '' && !isNaN(Number(rawTextSize)) ? Math.max(8, Number(rawTextSize)) : 18;

        var btnTextWeight = String(hs.stickerBtnTextWeight || hs['hotspot-sticker-btn-text-weight'] || '600');

        if (mainBgOn) {
            card.style.backgroundColor = wpvrFormatRgbaColor(rawBgColor, bgOpacity);
            card.style.backdropFilter = filterValue;
            card.style.webkitBackdropFilter = filterValue;

            if (rawBorderColor === 'none' || rawBorderColor === 'transparent') {
                card.style.border = 'none';
            } else {
                card.style.border = '1px solid ' + rawBorderColor;
            }
            card.style.borderRadius = wpvrFormatBorderRadius(borderRadius, 15);
            card.style.boxShadow = '0 12px 32px rgba(0, 0, 0, 0.45)';
            card.style.padding = wpvrFormatPadding(padding, 21);
        } else {
            card.classList.add('wpvr-sticker-card--no-main-bg');
            card.style.backgroundColor = 'transparent';
            card.style.backdropFilter = 'none';
            card.style.webkitBackdropFilter = 'none';
            card.style.border = 'none';
            card.style.boxShadow = 'none';
            card.style.padding = '0';
        }
        card.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        card.style.display = 'inline-flex';
        card.style.alignItems = 'center';
        card.style.justifyContent = 'space-between';
        card.style.gap = '20px';
        card.style.boxSizing = 'border-box';
        card.style.width = 'auto';
        card.style.maxWidth = 'none';

        // Left text section
        var textWrap = document.createElement('div');
        textWrap.className = 'wpvr-sticker-cart-text-wrap';
        textWrap.style.display = 'inline-flex';
        textWrap.style.alignItems = 'center';
        textWrap.style.gap = '6px';
        textWrap.style.whiteSpace = 'nowrap';
        textWrap.style.color = textColor;
        textWrap.style.fontSize = '18px';
        textWrap.style.lineHeight = '24px';
        textWrap.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');

        if (prefixText) {
            var prefixEl = document.createElement('span');
            prefixEl.className = 'wpvr-sticker-cart-prefix-text';
            prefixEl.textContent = prefixText;
            prefixEl.style.fontWeight = '700';
            prefixEl.style.color = textColor;
            textWrap.appendChild(prefixEl);
        }

        if (subText) {
            var subEl = document.createElement('span');
            subEl.className = 'wpvr-sticker-cart-sub-text';
            subEl.textContent = subText;
            subEl.style.fontWeight = '500';
            subEl.style.color = textColor;
            textWrap.appendChild(subEl);
        }

        var showProductText = hs.stickerShowPlaceholderText !== undefined ? hs.stickerShowPlaceholderText : hs['hotspot-sticker-show-placeholder-text'];
        if (showProductText !== 'off' && showProductText !== false && showProductText !== 'false') {
            card.appendChild(textWrap);
        }

        // Right button (link only on that button!)
        var btnEl;
        if (btnUrl) {
            btnEl = document.createElement('a');
            btnEl.href = btnUrl;
            btnEl.className = 'wpvr-sticker-cart-btn';
            if (btnNewTab === 'on' || btnNewTab === true || btnNewTab === 'true') {
                btnEl.target = '_blank';
                btnEl.rel = 'noopener noreferrer';
            }
        } else {
            btnEl = document.createElement('div');
            btnEl.className = 'wpvr-sticker-cart-btn';
        }
        btnEl.style.backgroundColor = buttonBackgroundOn ? btnColor : 'transparent';
        btnEl.style.boxShadow = buttonBackgroundOn ? '0px 10px 30px rgba(0, 0, 0, 0.25)' : 'none';
        btnEl.style.borderRadius = btnRadius + 'px';
        btnEl.style.display = 'inline-flex';
        btnEl.style.alignItems = 'center';
        btnEl.style.justifyContent = 'center';
        btnEl.style.gap = '8px';
        btnEl.style.padding = '0 24px';
        btnEl.style.height = btnHeight + 'px';
        btnEl.style.boxSizing = 'border-box';
        btnEl.style.cursor = 'pointer';
        btnEl.style.flexShrink = '0';
        btnEl.style.textDecoration = 'none';

        if (btnWidth) {
            btnEl.style.width = btnWidth + 'px';
        } else {
            btnEl.style.width = 'auto';
        }

        if (btnBorder > 0 && btnBorderColor !== 'none' && btnBorderColor !== 'transparent') {
            btnEl.style.border = btnBorder + 'px solid ' + btnBorderColor;
        } else {
            btnEl.style.border = 'none';
        }

        var btnLabel = document.createElement('span');
        btnLabel.className = 'wpvr-sticker-cart-btn-label';
        btnLabel.textContent = btnText;
        btnLabel.style.color = btnTextColor;
        btnLabel.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        btnLabel.style.fontWeight = btnTextWeight;
        btnLabel.style.fontSize = btnTextSize + 'px';
        btnLabel.style.lineHeight = '24px';
        btnLabel.style.whiteSpace = 'nowrap';
        btnEl.appendChild(btnLabel);

        btnEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        btnEl.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        btnEl.addEventListener('pointerdown', function(e) { e.stopPropagation(); });
        if (btnUrl) {
            btnEl.addEventListener('click', function(e) { e.stopPropagation(); });
        }
        card.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        card.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        card.addEventListener('pointerdown', function(e) { e.stopPropagation(); });

        card.appendChild(btnEl);
        wpvrAppendStickerCard(hotSpotDiv, card);
    } else if (template === 'social_share') {
        var card = document.createElement('div');
        card.className = 'wpvr-sticker-card wpvr-sticker-card--social-share';

        var mainBg = hs.stickerMainBg !== undefined ? hs.stickerMainBg : (hs['hotspot-sticker-main-bg'] !== undefined ? hs['hotspot-sticker-main-bg'] : 'on');
        var mainBgOn = mainBg !== 'off' && mainBg !== false && mainBg !== 'false';
        var rawBgColor = hs.stickerBgColor || hs['hotspot-sticker-bg-color'] || '#201b2c';
        var rawOpacity = hs.stickerBgOpacity !== undefined ? hs.stickerBgOpacity : hs['hotspot-sticker-bg-opacity'];
        var bgOpacity = rawOpacity !== undefined && rawOpacity !== '' ? Number(rawOpacity) : 75;
        var blur = Math.max(0, Math.min(40, Number(hs.stickerBlur || hs['hotspot-sticker-blur'] || 12)));
        var brightness = Math.max(0, Math.min(200, Number(hs.stickerBrightness || hs['hotspot-sticker-brightness'] || 100)));
        var brightnessRatio = Number((brightness / 100).toFixed(2));
        var filterValue = 'blur(' + blur + 'px) brightness(' + brightnessRatio + ')';

        var rawBorderColor = hs.stickerBorderColor !== undefined ? hs.stickerBorderColor : hs['hotspot-sticker-border-color'];
        if (rawBorderColor === undefined) rawBorderColor = '#40355a';

        var btnColor = hs.stickerBtnColor || hs['hotspot-sticker-btn-color'] || '#201a2b';
        var btnTextColor = hs.stickerBtnTextColor || hs['hotspot-sticker-btn-text-color'] || '#ffffff';
        var btnText = hs.stickerBtnText !== undefined ? hs.stickerBtnText : (hs['hotspot-sticker-btn-text'] !== undefined ? hs['hotspot-sticker-btn-text'] : 'SHARE EXPERIENCE');
        var showPlaceholder = hs.stickerShowPlaceholderText !== undefined ? hs.stickerShowPlaceholderText : hs['hotspot-sticker-show-placeholder-text'];
        if (showPlaceholder === undefined) {
            showPlaceholder = 'on';
        }
        var isPlaceholderVisible = showPlaceholder !== 'off' && showPlaceholder !== false && showPlaceholder !== 'false';

        var rawWidth = hs.stickerBtnWidth !== undefined ? hs.stickerBtnWidth : hs['hotspot-sticker-btn-width'];
        var btnWidth = rawWidth !== undefined && rawWidth !== '' && !isNaN(Number(rawWidth)) && Number(rawWidth) > 0 ? Number(rawWidth) : 222;

        var rawHeight = hs.stickerBtnHeight !== undefined ? hs.stickerBtnHeight : hs['hotspot-sticker-btn-height'];
        var btnHeight = rawHeight !== undefined && rawHeight !== '' && !isNaN(Number(rawHeight)) && Number(rawHeight) > 0 ? Number(rawHeight) : 60;

        var rawRadius = hs.stickerBtnRadius !== undefined ? hs.stickerBtnRadius : hs['hotspot-sticker-btn-radius'];
        var btnRadius = rawRadius !== undefined && rawRadius !== '' && !isNaN(Number(rawRadius)) ? Math.max(0, Number(rawRadius)) : 10;

        var rawBorder = hs.stickerBtnBorder !== undefined ? hs.stickerBtnBorder : hs['hotspot-sticker-btn-border'];
        var btnBorder = rawBorder !== undefined && rawBorder !== '' && !isNaN(Number(rawBorder)) ? Math.max(0, Number(rawBorder)) : 1;

        var btnBorderColor = hs.stickerBtnBorderColor || hs['hotspot-sticker-btn-border-color'] || '#3a3051';

        var rawTextSize = hs.stickerBtnTextSize !== undefined ? hs.stickerBtnTextSize : hs['hotspot-sticker-btn-text-size'];
        var btnTextSize = rawTextSize !== undefined && rawTextSize !== '' && !isNaN(Number(rawTextSize)) ? Math.max(8, Number(rawTextSize)) : 18;

        var btnTextWeight = String(hs.stickerBtnTextWeight || hs['hotspot-sticker-btn-text-weight'] || '600');

        var socialColor = hs.stickerSocialColor || hs['hotspot-sticker-social-color'] || '#ffffff';
        var rawSocialSize = hs.stickerSocialSize !== undefined ? hs.stickerSocialSize : hs['hotspot-sticker-social-size'];
        var socialSize = rawSocialSize !== undefined && rawSocialSize !== '' && !isNaN(Number(rawSocialSize)) ? Math.max(10, Number(rawSocialSize)) : 20;
        var rawSocialGap = hs.stickerSocialGap !== undefined ? hs.stickerSocialGap : hs['hotspot-sticker-social-gap'];
        var socialGap = rawSocialGap !== undefined && rawSocialGap !== '' && !isNaN(Number(rawSocialGap)) ? Math.max(0, Number(rawSocialGap)) : 16;

        var rawSocialLinks = hs.stickerSocialLinks !== undefined ? hs.stickerSocialLinks : hs['hotspot-sticker-social-links'];
        var socialLinks = [];
        if (typeof rawSocialLinks === 'string') {
            try {
                socialLinks = JSON.parse(rawSocialLinks);
            } catch (e) {
                socialLinks = [];
            }
        } else if (Array.isArray(rawSocialLinks)) {
            socialLinks = rawSocialLinks;
        }
        if (rawSocialLinks === undefined || rawSocialLinks === null) {
            socialLinks = [
                { id: '1', icon: 'fab fa-linkedin-in', customSvg: '', url: 'https://linkedin.com', openNewTab: 'on' },
                { id: '2', icon: 'fab fa-facebook-f', customSvg: '', url: 'https://facebook.com', openNewTab: 'on' },
                { id: '3', icon: 'fab fa-instagram', customSvg: '', url: 'https://instagram.com', openNewTab: 'on' },
                { id: '4', icon: 'fab fa-dribbble', customSvg: '', url: 'https://dribbble.com', openNewTab: 'on' }
            ];
        }

        if (mainBgOn) {
            card.style.backgroundColor = wpvrFormatRgbaColor(rawBgColor, bgOpacity);
            card.style.backdropFilter = filterValue;
            card.style.webkitBackdropFilter = filterValue;

            if (rawBorderColor === 'none' || rawBorderColor === 'transparent') {
                card.style.border = 'none';
            } else {
                card.style.border = '1px solid ' + rawBorderColor;
            }

            var radiusDef = hs.stickerBorderRadius !== undefined ? hs.stickerBorderRadius : hs['hotspot-sticker-border-radius'];
            card.style.borderRadius = wpvrFormatBorderRadius(radiusDef, 15);
            card.style.boxShadow = '0 12px 32px rgba(0, 0, 0, 0.45)';
            card.style.padding = wpvrFormatPadding(hs.stickerPadding !== undefined ? hs.stickerPadding : hs['hotspot-sticker-padding'], 21);
        } else {
            card.className += ' wpvr-sticker-card--no-main-bg';
            card.style.backgroundColor = 'transparent';
            card.style.backdropFilter = 'none';
            card.style.webkitBackdropFilter = 'none';
            card.style.border = 'none';
            card.style.boxShadow = 'none';
            card.style.padding = '0';
        }

        card.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
        card.style.display = 'inline-flex';
        card.style.alignItems = 'center';
        card.style.justifyContent = 'space-between';
        card.style.gap = '21px';
        card.style.boxSizing = 'border-box';
        card.style.width = 'auto';
        card.style.maxWidth = 'none';

        if (isPlaceholderVisible) {
            var btnEl = document.createElement('div');
            btnEl.className = 'wpvr-sticker-share-btn';
            btnEl.style.backgroundColor = buttonBackgroundOn ? btnColor : 'transparent';
            btnEl.style.boxShadow = buttonBackgroundOn ? '0px 10px 30px rgba(0, 0, 0, 0.25)' : 'none';
            btnEl.style.borderRadius = btnRadius + 'px';
            btnEl.style.display = 'inline-flex';
            btnEl.style.alignItems = 'center';
            btnEl.style.justifyContent = 'center';
            btnEl.style.padding = '0 20px';
            btnEl.style.height = btnHeight + 'px';
            btnEl.style.boxSizing = 'border-box';
            btnEl.style.cursor = 'default';
            btnEl.style.flexShrink = '0';
            btnEl.style.textDecoration = 'none';

            if (btnWidth) {
                btnEl.style.width = btnWidth + 'px';
            } else {
                btnEl.style.width = 'auto';
            }

            if (btnBorder > 0 && btnBorderColor !== 'none' && btnBorderColor !== 'transparent') {
                btnEl.style.border = btnBorder + 'px solid ' + btnBorderColor;
            } else {
                btnEl.style.border = 'none';
            }

            var btnLabel = document.createElement('span');
            btnLabel.className = 'wpvr-sticker-share-btn-label';
            btnLabel.textContent = btnText;
            btnLabel.style.color = btnTextColor;
            btnLabel.style.setProperty('font-family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'important');
            btnLabel.style.fontWeight = btnTextWeight;
            btnLabel.style.fontSize = btnTextSize + 'px';
            btnLabel.style.lineHeight = '24px';
            btnLabel.style.letterSpacing = '0.36px';
            btnLabel.style.textTransform = 'uppercase';
            btnLabel.style.whiteSpace = 'nowrap';
            btnEl.appendChild(btnLabel);

            btnEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
            btnEl.addEventListener('touchstart', function(e) { e.stopPropagation(); });
            btnEl.addEventListener('pointerdown', function(e) { e.stopPropagation(); });

            card.appendChild(btnEl);
        }

        var listEl = document.createElement('div');
        listEl.className = 'wpvr-sticker-social-list';
        listEl.style.display = 'inline-flex';
        listEl.style.alignItems = 'center';
        listEl.style.gap = socialGap + 'px';

        socialLinks.forEach(function(item) {
            var linkEl = document.createElement('a');
            linkEl.className = 'wpvr-sticker-social-link';
            linkEl.style.color = socialColor;
            linkEl.style.fontSize = socialSize + 'px';
            if (item.url) {
                linkEl.href = item.url;
                if (item.openNewTab !== 'off' && item.openNewTab !== false && item.openNewTab !== 'false') {
                    linkEl.target = '_blank';
                    linkEl.rel = 'noopener noreferrer';
                }
            }
            if (item.customSvg && typeof item.customSvg === 'string' && item.customSvg.trim().indexOf('<svg') === 0) {
                var svgSpan = document.createElement('span');
                svgSpan.className = 'wpvr-sticker-social-svg';
                svgSpan.style.width = socialSize + 'px';
                svgSpan.style.height = socialSize + 'px';
                svgSpan.style.display = 'inline-flex';
                svgSpan.style.alignItems = 'center';
                svgSpan.style.justifyContent = 'center';
                svgSpan.innerHTML = item.customSvg.trim();
                linkEl.appendChild(svgSpan);
            } else if (item.icon) {
                var iconEl = document.createElement('i');
                iconEl.className = item.icon;
                iconEl.style.fontSize = socialSize + 'px';
                iconEl.style.lineHeight = '1';
                iconEl.style.color = socialColor;
                if (item.icon.indexOf('fab') !== -1) {
                    iconEl.style.setProperty('font-family', '"Font Awesome 6 Brands", "Font Awesome 5 Brands"', 'important');
                    iconEl.style.setProperty('font-weight', '400', 'important');
                }
                linkEl.appendChild(iconEl);
            }

            linkEl.addEventListener('click', function(e) { e.stopPropagation(); });
            linkEl.addEventListener('mousedown', function(e) { e.stopPropagation(); });
            linkEl.addEventListener('touchstart', function(e) { e.stopPropagation(); });
            linkEl.addEventListener('pointerdown', function(e) { e.stopPropagation(); });
            listEl.appendChild(linkEl);
        });

        card.appendChild(listEl);

        card.addEventListener('mousedown', function(e) { e.stopPropagation(); });
        card.addEventListener('touchstart', function(e) { e.stopPropagation(); });
        card.addEventListener('pointerdown', function(e) { e.stopPropagation(); });

        wpvrAppendStickerCard(hotSpotDiv, card);
    }
}
window.wpvrRenderStickerHotspot = wpvrRenderStickerHotspot;
