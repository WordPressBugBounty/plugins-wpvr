(function ($) {
    'use strict';

    function readJSON(value, fallback) {
        try { return JSON.parse(value); } catch (e) { return fallback; }
    }

    function enhanceIcon(input) {
        input.addClass('wpvr-sticker-icon-value');
        var control = input.parent().find('> .wpvr-sticker-icon-control');
        if (!control.length) {
            control = $('<div class="wpvr-sticker-icon-control"><button type="button" class="wpvr-sticker-icon-trigger" aria-expanded="false"><i></i><span></span></button><div class="wpvr-sticker-icon-menu" hidden><input type="search" class="wpvr-sticker-icon-search" placeholder="Search icons" aria-label="Search icons"><div class="wpvr-sticker-icon-grid"></div></div></div>');
            input.after(control);
        }
        var icon = input.val() || 'none';
        control.find('.wpvr-sticker-icon-trigger i').attr('class', icon === 'none' ? '' : icon);
        control.find('.wpvr-sticker-icon-trigger span').text(icon === 'none' ? 'No icon' : icon.split(' ').pop().replace(/^fa-/, '').replace(/-/g, ' '));
    }

    function enhanceField(field) {
        var input = field.find('.wpvr-sticker-value');
        var name = input.attr('name') || '';
        var kind = field.attr('data-kind');
        if (/-color\]?$/i.test(name)) {
            if (!field.find('.wpvr-sticker-color-control').length) {
                input.wrap('<div class="wpvr-sticker-color-control"></div>');
                $('<input type="color" class="wpvr-sticker-color-swatch" aria-label="Choose color">').insertBefore(input);
            }
            var color = /^#[0-9a-f]{6}$/i.test(input.val()) ? input.val() : '#ffffff';
            field.find('.wpvr-sticker-color-swatch').val(color);
        }
        var numberInput = field.find('.wpvr-sticker-number');
        if (kind === 'number') numberInput.val(input.val());
        if (kind === 'number' && /(?:rating|bg-opacity|blur|brightness)\]?$/.test(name)) {
            if (!field.find('.wpvr-sticker-range-control').length) {
                numberInput.wrap('<div class="wpvr-sticker-range-control"></div>');
                $('<input type="range" class="wpvr-sticker-range" aria-label="Adjust value">')
                    .attr('min', numberInput.attr('min')).attr('max', numberInput.attr('max')).attr('step', numberInput.attr('step')).insertBefore(numberInput);
            }
            field.find('.wpvr-sticker-range').val(input.val());
        }
        if (kind === 'toggle') {
            input.addClass('wpvr-sticker-toggle-value');
            if (!field.find('.wpvr-sticker-switch').length) {
                $('<button type="button" role="switch" class="wpvr-sticker-switch"></button><span class="wpvr-sticker-switch-state"></span>').insertAfter(input);
            }
            var on = input.val() === 'on';
            field.find('.wpvr-sticker-switch').attr('aria-checked', String(on)).attr('aria-label', field.find('.wpvr-sticker-field-label').text());
            field.find('.wpvr-sticker-switch-state').text(on ? 'On' : 'Off');
        }
        if (kind === 'media') {
            if (!field.find('.wpvr-sticker-avatar-preview').length) $('<img class="wpvr-sticker-avatar-preview" alt="Client avatar">').insertBefore(field.find('.wpvr-sticker-avatar-select'));
            field.find('.wpvr-sticker-avatar-preview').attr('src', input.val() || '').toggle(!!input.val());
        }
        if (/btn-icon\]?$/.test(name)) enhanceIcon(input);
    }

    $(document).on('input change', '.wpvr-sticker-color-swatch, .wpvr-sticker-range, .wpvr-sticker-number', function () {
        $(this).closest('.wpvr-sticker-field').find('.wpvr-sticker-value').val($(this).val()).trigger('change');
    });
    $(document).on('change input', '.wpvr-sticker-value', function () { enhanceField($(this).closest('.wpvr-sticker-field')); });
    $(document).on('click', '.wpvr-sticker-switch', function () {
        var input = $(this).closest('.wpvr-sticker-field').find('.wpvr-sticker-value');
        input.val(input.val() === 'on' ? 'off' : 'on').trigger('change');
    });
    function iconChoices() {
        var icons = ['none', 'fas fa-tag', 'fas fa-shopping-cart', 'fas fa-arrow-right', 'fas fa-link', 'fas fa-star', 'fas fa-heart', 'fas fa-info-circle', 'fas fa-play', 'fas fa-phone', 'fas fa-envelope', 'fas fa-map-marker-alt', 'fas fa-gift', 'fas fa-ticket-alt', 'fas fa-share-alt', 'fab fa-facebook-f', 'fab fa-instagram', 'fab fa-linkedin-in', 'fab fa-twitter', 'fab fa-dribbble', 'fab fa-youtube', 'fab fa-whatsapp'];
        $('.hotspot-customclass-pro-select').first().find('option').each(function () {
            if (this.value && icons.indexOf(this.value) === -1) icons.push(this.value);
        });
        return icons;
    }
    function filterIcons(control) {
        var term = control.find('.wpvr-sticker-icon-search').val().toLowerCase();
        var input = control.prev('input');
        var grid = control.find('.wpvr-sticker-icon-grid').empty();
        iconChoices().filter(function (icon) { return icon.indexOf(term) !== -1; }).forEach(function (icon) {
            var button = $('<button type="button" class="wpvr-sticker-icon-option"></button>').attr('data-icon', icon).attr('title', icon).attr('aria-label', icon).attr('aria-pressed', String(input.val() === icon));
            if (icon === 'none') button.text('×'); else $('<i></i>').attr('class', icon).appendTo(button);
            grid.append(button);
        });
    }
    $(document).on('click', '.wpvr-sticker-icon-trigger', function () {
        var control = $(this).closest('.wpvr-sticker-icon-control');
        var open = $(this).attr('aria-expanded') !== 'true';
        $(this).attr('aria-expanded', String(open));
        control.find('.wpvr-sticker-icon-menu').prop('hidden', !open);
        if (open) { filterIcons(control); control.find('.wpvr-sticker-icon-search').focus(); }
    });
    $(document).on('input', '.wpvr-sticker-icon-search', function () { filterIcons($(this).closest('.wpvr-sticker-icon-control')); });
    $(document).on('click', '.wpvr-sticker-icon-option', function () {
        var control = $(this).closest('.wpvr-sticker-icon-control');
        var input = control.prev('input');
        input.val($(this).attr('data-icon')).trigger('input').trigger('change');
        enhanceIcon(input);
        control.find('.wpvr-sticker-icon-menu').prop('hidden', true);
        control.find('.wpvr-sticker-icon-trigger').attr('aria-expanded', 'false').focus();
    });
    $(document).on('keydown', '.wpvr-sticker-icon-control', function (event) {
        if (event.key === 'Escape') {
            $(this).find('.wpvr-sticker-icon-menu').prop('hidden', true);
            $(this).find('.wpvr-sticker-icon-trigger').attr('aria-expanded', 'false').focus();
        }
    });

    function renderLinks(field) {
        var links = readJSON(field.find('.wpvr-sticker-value').val(), []);
        var list = field.find('.wpvr-sticker-links').empty();
        links.forEach(function (link) {
            var row = $('<div class="wpvr-sticker-link" style="margin:10px 0"></div>').appendTo(list);
            [['icon', 'Icon class'], ['customSvg', 'Custom SVG'], ['url', 'URL']].forEach(function (item) {
                $('<input type="text" style="width:100%;margin-bottom:5px">')
                    .attr('data-link-key', item[0]).attr('aria-label', item[1]).attr('placeholder', item[1])
                    .val(link[item[0]] || '').appendTo(row);
            });
            $('<select data-link-key="openNewTab" aria-label="Open in new tab"><option value="on">New tab</option><option value="off">Same tab</option></select>')
                .val(link.openNewTab || 'on').appendTo(row);
            $('<button type="button" class="button wpvr-sticker-link-remove" aria-label="Remove social link">×</button>').appendTo(row);
            row.data('link-id', link.id);
            enhanceIcon(row.find('[data-link-key=icon]'));
        });
    }

    function syncCompound(field) {
        var kind = field.attr('data-kind');
        var value = readJSON(field.find('.wpvr-sticker-value').val(), {});
        if (kind === 'corners' || kind === 'sides') {
            field.find('[data-part]').each(function () {
                $(this).val(value[$(this).attr('data-part')] || 0);
            });
        } else if (kind === 'links') {
            renderLinks(field);
        }
    }

    function updateSettings(hotspot, changeTemplate) {
        var panel = hotspot.find('.wpvr-legacy-sticker-settings');
        var type = hotspot.find('select[name*="hotspot-type"]').val();
        var select = panel.find('.wpvr-sticker-template');
        var template = select.val() || 'social_proof';
        var previous = panel.data('active-template') || 'social_proof';
        var fresh = !select.val();
        select.val(template);
        panel.toggle(type === 'sticker');
        if (type === 'sticker') {
            hotspot.find('.hotspot-scene, .hotspot-url, .s_tab, .hotspot-content, .hotspot-products, .hotspot-fluent-forms, .hotspot-hover').hide();
        } else {
            hotspot.find('.hotspot-hover').show();
        }
        panel.find('.wpvr-sticker-field').each(function () {
            var field = $(this);
            var defaults = readJSON(field.attr('data-defaults'), {});
            var input = field.find('.wpvr-sticker-value');
            var oldDefault = typeof defaults[previous] === 'object' ? JSON.stringify(defaults[previous]) : String(defaults[previous]);
            var newDefault = typeof defaults[template] === 'object' ? JSON.stringify(defaults[template]) : defaults[template];
            if (fresh || (changeTemplate && String(input.val()) === oldDefault)) input.val(newDefault);
            var applies = field.attr('data-templates');
            field.toggle(!applies || applies.split(' ').indexOf(template) !== -1);
            syncCompound(field);
            enhanceField(field);
        });
        panel.find('.wpvr-sticker-section').each(function () {
            var applies = $(this).attr('data-templates');
            $(this).toggle(!applies || applies.split(' ').indexOf(template) !== -1);
        });
        panel.data('active-template', template);
    }

    // Runs after either Free or Pro's legacy handler, which handles the other types.
    $(document).on('change', 'select[name*="hotspot-type"], .wpvr-sticker-template', function () {
        updateSettings($(this).closest('.single-hotspot'), $(this).hasClass('wpvr-sticker-template'));
    });
    $(document).on('input change', '.wpvr-sticker-field [data-part]', function () {
        var field = $(this).closest('.wpvr-sticker-field');
        var value = {};
        field.find('[data-part]').each(function () { value[$(this).attr('data-part')] = Math.max(0, Number($(this).val()) || 0); });
        field.find('.wpvr-sticker-value').val(JSON.stringify(value));
    });
    function saveLinks(field) {
        var links = [];
        field.find('.wpvr-sticker-link').each(function () {
            var row = $(this);
            var link = { id: row.data('link-id') || String(Date.now()) + '-' + links.length };
            row.find('[data-link-key]').each(function () { link[$(this).attr('data-link-key')] = $(this).val(); });
            links.push(link);
        });
        field.find('.wpvr-sticker-value').val(JSON.stringify(links));
    }
    $(document).on('input change', '.wpvr-sticker-field [data-link-key]', function () {
        saveLinks($(this).closest('.wpvr-sticker-field'));
    });
    $(document).on('click', '.wpvr-sticker-link-add, .wpvr-sticker-link-remove', function () {
        var field = $(this).closest('.wpvr-sticker-field');
        if ($(this).hasClass('wpvr-sticker-link-remove')) {
            $(this).closest('.wpvr-sticker-link').remove();
            saveLinks(field);
        } else {
            var input = field.find('.wpvr-sticker-value');
            var links = readJSON(input.val(), []);
            links.push({ id: String(Date.now()), icon: 'fab fa-facebook-f', customSvg: '', url: '', openNewTab: 'on' });
            input.val(JSON.stringify(links));
            renderLinks(field);
        }
    });
    $(document).on('click', '.wpvr-sticker-avatar-select, .wpvr-sticker-avatar-remove', function () {
        var input = $(this).closest('.wpvr-sticker-field').find('.wpvr-sticker-value');
        if ($(this).hasClass('wpvr-sticker-avatar-remove')) { input.val('').trigger('change'); return; }
        var frame = wp.media({ library: { type: 'image' }, multiple: false });
        frame.on('select', function () { input.val(frame.state().get('selection').first().toJSON().url).trigger('change'); });
        frame.open();
    });
    $(function () {
        $('.single-hotspot').each(function () { updateSettings($(this), false); });
        var root = document.querySelector('.scene-setup');
        if (!root) return;
        // Repeaters clear cloned fields; initialize each new hotspot after that reset.
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return;
                    $(node).find('.single-hotspot').addBack('.single-hotspot').each(function () {
                        updateSettings($(this), false);
                    });
                });
            });
        }).observe(root, { childList: true, subtree: true });
    });
})(jQuery);
