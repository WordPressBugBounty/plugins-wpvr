(function( $ ) {
    'use strict';

    $(document).ready(function(){

        var main_tour        = new Shepherd.Tour();
        var guide_tranlation = window.wpvr_tour_guide_obj.Tour_Guide_Translation;

        // ── Styles ───────────────────────────────────────────────────────────
        var styleEl = document.createElement('style');
        styleEl.innerHTML = `
            /* ─── Modal ────────────────────────────────────────────────────── */
            .shepherd-element .shepherd-content {
                border-radius: 16px !important;
                background: #F6F4FF !important;
                box-shadow: 0 12px 48px rgba(63,4,254,.20) !important;
                min-width: 400px;
                max-width: 480px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .shepherd-background-step-1 .shepherd-content {
                background-image: url('` + window.wpvr_tour_guide_obj.step1_bg_image + `') !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
            }
            .shepherd-cancel-icon { display: none !important; }
            .shepherd-header { padding: 22px 22px 6px; }
            .shepherd-text  { padding: 6px 22px 14px; }

            /* ─── Typography ───────────────────────────────────────────────── */
            p.wpvr-guided-tour-title {
                font-weight: 700; font-size: 20px; line-height: 1.25;
                text-align: center; color: #3F04FE; margin: 0 0 10px;
            }
            p.wpvr-guided-tour-title-left {
                font-weight: 700; font-size: 18px; line-height: 1.25;
                color: #3F04FE; margin: 0 0 10px;
            }
            p.wpvr-guided-tour-description {
                font-weight: 400; font-size: 14px; line-height: 1.65;
                text-align: center; color: #4b5563; margin: 0;
            }
            p.wpvr-guided-tour-description strong,
            p.wpvr-guided-tour-description span { color: #3F04FE; font-weight: 600; }

            /* ─── Instruction lists ─────────────────────────────────────────── */
            .hotspot-instructions { text-align: left; }
            .hotspot-intro  { color:#4b5563; font-size:14px; line-height:1.65; margin-bottom:10px; }
            .hotspot-steps  { padding-left:18px; margin:0; color:#4b5563; font-size:14px; line-height:1.9; }
            .hotspot-steps strong { color: #3F04FE; }
            .help-icon {
                display:inline-flex; align-items:center; justify-content:center;
                width:15px; height:15px; background:#3F04FE; color:#fff;
                border-radius:50%; font-size:10px; font-weight:700; cursor:help; vertical-align:middle;
            }

            /* ─── Progress bar ──────────────────────────────────────────────── */
            .wpvr-tour-progress {
                height:4px; background:#e5e7eb; border-radius:99px;
                margin:0 22px 14px; overflow:hidden;
            }
            .wpvr-tour-progress-fill {
                height:100%; background:linear-gradient(90deg,#3F04FE,#7c3aed);
                border-radius:99px; transition:width .35s ease;
            }

            /* ─── Footer ────────────────────────────────────────────────────── */
            .shepherd-footer { padding: 0 22px 22px; }
            .shepherd-buttons {
                display:flex; align-items:center; list-style:none; padding:0; margin:0;
            }
            .shepherd-buttons li              { flex: 0 0 auto; }
            .shepherd-buttons li.wpvr-counter-li { flex:1; text-align:center; }
            .wpvr-step-counter {
                font-size:12px; font-weight:600; color:#9ca3af; letter-spacing:0.4px;
            }

            /* ─── Skip Tour — text link ─────────────────────────────────────── */
            a.shepherd-button.wpvr-btn-skip-tour {
                background:none !important; border:none !important; box-shadow:none !important;
                color:#9ca3af !important; font-size:13px !important; font-weight:500 !important;
                padding:0 !important; cursor:pointer !important;
                text-decoration:underline !important; text-underline-offset:2px !important;
                line-height:1 !important; display:inline-flex !important; align-items:center !important;
            }
            a.shepherd-button.wpvr-btn-skip-tour:hover { color:#6b7280 !important; }

            /* ─── Skip Step — ghost ─────────────────────────────────────────── */
            a.shepherd-button.wpvr-btn-skip-step {
                background:transparent !important; border:1.5px solid #d1d5db !important;
                border-radius:8px !important; color:#374151 !important;
                font-size:13px !important; font-weight:500 !important;
                padding:6px 16px !important; cursor:pointer !important;
                line-height:1.4 !important; margin-right:8px !important;
                display:inline-flex !important; align-items:center !important;
            }
            a.shepherd-button.wpvr-btn-skip-step:hover {
                border-color:#9ca3af !important; color:#111827 !important;
            }

            /* ─── Next — solid primary ──────────────────────────────────────── */
            a.shepherd-button.wpvr-btn-next {
                background:#3F04FE !important; border:2px solid #3F04FE !important;
                border-radius:8px !important; color:#fff !important;
                font-size:13px !important; font-weight:600 !important;
                padding:6px 18px !important; cursor:pointer !important;
                line-height:1.4 !important; display:inline-flex !important;
                align-items:center !important; gap:6px !important; text-decoration:none !important;
            }
            a.shepherd-button.wpvr-btn-next:hover {
                background:#2b00cc !important; border-color:#2b00cc !important;
            }

            /* ─── Next — DISABLED state ─────────────────────────────────────── */
            a.shepherd-button.wpvr-btn-next.wpvr-btn-next--disabled {
                background:#c4b5fd !important; border-color:#c4b5fd !important;
                color:#fff !important; cursor:not-allowed !important;
                pointer-events:none !important; opacity:.7 !important;
            }

            .wpvr-next-arrow svg { display:block; }
        `;
        document.head.appendChild(styleEl);

        // ── Tour defaults ────────────────────────────────────────────────────
        main_tour.options.defaults = {
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour shadow-md',
            showCancelLink: false,
            useModalOverlay: true,
            scrollTo: true,
            tetherOptions: {
                constraints: [{ to: 'scrollParent', attachment: 'together', pin: false }]
            }
        };

        // ── Strings ──────────────────────────────────────────────────────────
        var nextTxt     = guide_tranlation.next_button_text;
        var skipTourTxt = guide_tranlation.end_tour  || 'Skip Tour';
        var skipStepTxt = 'Skip';
        var doneTxt     = guide_tranlation.done_text || 'Done';

        var arrowSvg = '<span class="wpvr-next-arrow"><svg width="13" height="11" viewBox="0 0 13 11" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                       '<path d="M7.5 9.5L11.5 5.5L7.5 1.5" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>' +
                       '<path d="M1.5 5.5H11.5" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';

        // ── Footer builder ───────────────────────────────────────────────────
        function buildFooter(stepEl, currentStep, totalSteps) {
            var footer = stepEl.querySelector('footer');
            if (!footer) return;

            var existingBar = stepEl.querySelector('.wpvr-tour-progress');
            if (existingBar) existingBar.remove();
            var barEl = document.createElement('div');
            barEl.className = 'wpvr-tour-progress';
            barEl.innerHTML  = '<div class="wpvr-tour-progress-fill" style="width:' +
                               Math.round((currentStep / totalSteps) * 100) + '%"></div>';
            footer.parentNode.insertBefore(barEl, footer);

            var buttonsList = footer.querySelector('.shepherd-buttons');
            if (!buttonsList) return;
            var existingCounter = buttonsList.querySelector('.wpvr-counter-li');
            if (existingCounter) existingCounter.remove();
            var counterLi = document.createElement('li');
            counterLi.className = 'wpvr-counter-li';
            counterLi.innerHTML  = '<span class="wpvr-step-counter">' + currentStep + ' / ' + totalSteps + '</span>';
            var firstBtn = buttonsList.querySelector('li');
            if (firstBtn && firstBtn.nextSibling) {
                buttonsList.insertBefore(counterLi, firstBtn.nextSibling);
            } else {
                buttonsList.appendChild(counterLi);
            }

            var nextBtn = buttonsList.querySelector('.wpvr-btn-next');
            if (nextBtn && !nextBtn.querySelector('.wpvr-next-arrow')) {
                nextBtn.insertAdjacentHTML('beforeend', arrowSvg);
            }
        }

        // ── Disable / enable Next ────────────────────────────────────────────
        function disableNext(stepEl) {
            var btn = stepEl && stepEl.querySelector('a.wpvr-btn-next');
            if (btn) btn.classList.add('wpvr-btn-next--disabled');
        }
        function enableNext(stepEl) {
            var btn = stepEl && stepEl.querySelector('a.wpvr-btn-next');
            if (btn) btn.classList.remove('wpvr-btn-next--disabled');
        }

        /**
         * Poll checkFn every 400 ms; call onMet() once when it returns true.
         * Automatically stops when the step element leaves the DOM (user skipped).
         */
        function watchCondition(stepEl, checkFn, onMet) {
            if (checkFn()) { onMet(); return; }
            var timer = setInterval(function() {
                if (!stepEl.parentNode || main_tour.canceled) {
                    clearInterval(timer); return;
                }
                if (checkFn()) { clearInterval(timer); onMet(); }
            }, 400);
        }

        // ── Show handler with disabled Next + watcher ────────────────────────
        function onShowWithCondition(checkFn) {
            return function() {
                scroll_to_popup();
                var idx    = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;
                var stepEl = this.el;
                buildFooter(stepEl, idx, main_tour.steps.length);
                disableNext(stepEl);
                watchCondition(stepEl, checkFn, function() { enableNext(stepEl); });
            };
        }

        // ── Button factories ─────────────────────────────────────────────────
        function btnSkipTour() {
            return { classes: 'wpvr-btn-skip-tour', text: skipTourTxt, action: main_tour.cancel };
        }
        function btnSkipStep() {
            return { classes: 'wpvr-btn-skip-step', text: skipStepTxt, action: main_tour.next };
        }
        function btnNext(action) {
            return { classes: 'wpvr-btn-next', text: nextTxt, action: action || main_tour.next };
        }
        function btnDone() {
            return { classes: 'wpvr-btn-next', text: doneTxt, action: function() { main_tour.complete(); } };
        }

        // Generic show handler (no required action — Next always enabled)
        function onShow() {
            return function() {
                scroll_to_popup();
                var idx = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;
                buildFooter(this.el, idx, main_tour.steps.length);
            };
        }

        // ════════════════════════════════════════════════════════════════════
        // STEP 1 — Name Your Tour
        // Next disabled until #title has text
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('tour_title', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Name Your Tour</p>" +
                  "<p class='wpvr-guided-tour-description'>Give your virtual tour a memorable title. This helps you find it later in your tour list.</p>",
            attachTo: '#post-body-content bottom',
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: function() {
                    scroll_to_popup();
                    var idx    = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;
                    var stepEl = this.el;
                    buildFooter(stepEl, idx, main_tour.steps.length);

                    disableNext(stepEl);
                    var titleEl = document.getElementById('title');
                    function check() { return titleEl && titleEl.value.trim().length > 0; }
                    watchCondition(stepEl, check, function() { enableNext(stepEl); });

                    if (titleEl) {
                        $(titleEl).on('input.wpvr_tour', function() {
                            if (check()) { enableNext(stepEl); $(titleEl).off('input.wpvr_tour'); }
                        });
                    }
                }
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 2 — Great! (confirmation)
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('tour_title_given', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Great!</p>" +
                  "<p class='wpvr-guided-tour-description'>Your tour has a name. You're ready to start building your first scene!</p>",
            attachTo: '#post-body-content bottom',
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: { show: onShow() }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 3 — Upload Scene
        // Next disabled until .scene-attachment-url has a value
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('upload_image', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Add Your First Scene</p>" +
                  "<p class='wpvr-guided-tour-description'>Click the upload area and choose your panorama image. Scene IDs are auto-generated but you can customise them (e.g. <strong>S1</strong>).</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '.single-scene.rex-pano-tab.active .scene-upload right',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: onShowWithCondition(function() {
                    var inp = document.querySelector('.single-scene.rex-pano-tab.active .scene-attachment-url');
                    return inp && inp.value.trim() !== '';
                })
            }
        });

        // Auto-resume after media library upload completes
        $('.single-scene.rex-pano-tab.active .scene-upload').on('click', function() {
            main_tour.hide();
            var $wrap        = $(this).closest('.single-scene.rex-pano-tab.active');
            var $urlInput    = $wrap.find('.scene-attachment-url');
            var initialValue = $urlInput.val();

            var resumeTimer = setInterval(function() {
                if (main_tour.canceled) { clearInterval(resumeTimer); return; }
                var val = $urlInput.val();
                if (val && val.trim() !== '' && val !== initialValue) {
                    clearInterval(resumeTimer);
                    $wrap.find('.wpvr_continue_guide').remove();
                    main_tour.show('upload_image_complete');
                    $('body').addClass('shepherd-active');
                }
            }, 500);
            setTimeout(function() { clearInterval(resumeTimer); }, 600000);

            if ($wrap.find('.wpvr_continue_guide').length === 0 && !main_tour.canceled) {
                $wrap.append('<span class="wpvr_continue_guide">Continue guide</span>');
            }
        });
        $(document).on('click', 'span.wpvr_continue_guide', function() {
            $(this).remove();
            main_tour.show('upload_image_complete');
            $('body').addClass('shepherd-active');
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 4 — Awesome! Scene ready (confirmation)
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('upload_image_complete', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Awesome!</p>" +
                  "<p class='wpvr-guided-tour-description'>Your first scene is ready — and it's already previewing in 360°! Now let's make your tour interactive with a hotspot.</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            attachTo: '.single-scene.rex-pano-tab.active .scene-upload right',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: { show: onShow() }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 5 — Open the Hotspot tab
        // Next disabled until user clicks the hotspot tab (li.hotspot.active)
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('navigate_hotspot_tab', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Open the Hotspot Tab</p>" +
                  "<p class='wpvr-guided-tour-description'>Click the <strong>Hotspot</strong> tab in the panel to enter the hotspot editor.</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '.rex-pano-nav-menu.main-nav ul li.hotspot bottom',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: onShowWithCondition(function() {
                    return document.querySelector('.rex-pano-nav-menu.main-nav ul li.hotspot.active') !== null;
                })
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 6 — Hotspot instructions
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('add_hotspot_section', {
            title: '',
            text: "<p class='wpvr-guided-tour-title-left'>Add Your First Hotspot</p>" +
                  "<div class='hotspot-instructions'>" +
                  "<p class='hotspot-intro'>Hotspots make your tour interactive — show text, images, videos or links when visitors click them.</p>" +
                  "<ol class='hotspot-steps'>" +
                  "<li><strong>Drag</strong> the panorama to find the exact spot.</li>" +
                  "<li>Click the <strong><i class='fas fa-plus-circle' style='color:#3F04FE;'></i> add-pitch icon</strong> to lock in the coordinates.</li>" +
                  "<li>Set <strong>click</strong> and <strong>hover</strong> content.</li>" +
                  "</ol></div>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#wpvr_item_builder__box left',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: function() {
                    scroll_to_popup();
                    var idx = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;
                    buildFooter(this.el, idx, main_tour.steps.length);
                    // Ensure hotspot tab is active
                    setTimeout(function() {
                        var tab = document.querySelector('.rex-pano-nav-menu.main-nav ul li.hotspot span');
                        if (tab) tab.click();
                    }, 150);
                }
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 7 — Drag panorama to find the spot
        // Next disabled until #panodata contains Pitch: and Yaw: values
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('drag_to_spot', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Find Your Perfect Spot</p>" +
                  "<p class='wpvr-guided-tour-description'>" +
                  "<strong>Click and drag</strong> inside the 360° preview to look around. " +
                  "Once the panorama shows where you want to place the hotspot, <strong>Next</strong> will unlock." +
                  "</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#wpvr_item_builder__box left',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: onShowWithCondition(function() {
                    var panodata = document.getElementById('panodata');
                    if (!panodata) return false;
                    var text = panodata.textContent || '';
                    return text.indexOf('Pitch:') !== -1 && text.indexOf('Yaw:') !== -1;
                })
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 8 — Click the add-pitch icon (.toppitch)
        // Next DISABLED until input.hotspot-pitch has a non-empty value.
        // Attached to .add-pitch so Shepherd cuts the overlay hole there.
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('set_hotspot_pitch', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Set Hotspot Coordinates</p>" +
                  "<p class='wpvr-guided-tour-description'>" +
                  "Click the <strong><i class='fas fa-plus-circle' style='color:#3F04FE;'></i> blue plus icon</strong> " +
                  "next to the coordinates in the panorama. " +
                  "It will instantly capture the <strong>Pitch &amp; Yaw</strong> for your hotspot. " +
                  "Next will unlock automatically once they are set." +
                  "</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '.add-pitch left',
            tetherOptions: {
                offset: '0 50px',
                constraints: [{ to: 'scrollParent', attachment: 'together', pin: false }]
            },
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: onShowWithCondition(function() {
                    var pitchInput = document.querySelector('#scene-1-hotspot-1 input.hotspot-pitch');
                    return pitchInput && pitchInput.value.trim() !== '';
                })
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 9 — Set Hotspot Content (click + hover in one step)
        // Next disabled until any of: URL, on-click content, or on-hover
        // content has a value.
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('on_click_content_info', {
            title: '',
            text: "<p class='wpvr-guided-tour-title-left'>Set Hotspot Content</p>" +
                  "<p class='wpvr-guided-tour-description' style='text-align:left;'>" +
                  "Fill in what visitors see when interacting with the hotspot:<br><br>" +
                  "• <strong>URL</strong> — link to an external page<br>" +
                  "• <strong>On Click Content</strong> — text, images, or video shown on click<br>" +
                  "• <strong>On Hover Content</strong> — tooltip preview on hover<br><br>" +
                  "Fill in at least one field, then proceed." +
                  "</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#scene-1-hotspot-1 .hotspot-type.hotspot-setting:not(.hotspot-hover) top',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: onShowWithCondition(function() {
                    var container = document.getElementById('scene-1-hotspot-1');
                    if (!container) return false;

                    // Check URL input
                    var urlInput = container.querySelector('input[name*="hotspot-url"]');
                    if (urlInput && urlInput.value.trim() !== '') return true;

                    // Check on-click content textarea (may be summernote)
                    var clickTextarea = container.querySelector('.hotspot-content textarea');
                    if (clickTextarea && clickTextarea.value.trim() !== '') return true;

                    // Check summernote rich editor for on-click content
                    var clickEditor = container.querySelector('.hotspot-content .note-editable');
                    if (clickEditor) {
                        var html = clickEditor.innerHTML.trim();
                        if (html !== '' && html !== '<br>' && html !== '<p><br></p>') return true;
                    }

                    // Check on-hover content textarea
                    var hoverTextarea = container.querySelector('.hotspot-hover textarea');
                    if (hoverTextarea && hoverTextarea.value.trim() !== '') return true;

                    // Check summernote rich editor for on-hover content
                    var hoverEditor = container.querySelector('.hotspot-hover .note-editable');
                    if (hoverEditor) {
                        var html2 = hoverEditor.innerHTML.trim();
                        if (html2 !== '' && html2 !== '<br>' && html2 !== '<p><br></p>') return true;
                    }

                    return false;
                })
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 10 — Hotspot done (confirmation)
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('add_hotspot_section_completed', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Nice!</p>" +
                  "<p class='wpvr-guided-tour-description'>You added your first interactive hotspot. Your tour is now engaging and ready to publish!</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            attachTo: '#wpvr_item_builder__box left',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: { show: onShow() }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 11 — Publish & Embed
        // Next disabled until the Publish button is clicked (post is saved)
        // Detected by checking if the URL changes to include "post=..." (new
        // post gets a real ID) or if #original_post_status becomes "publish".
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('publish_tour', {
            title: '',
            text: "<p class='wpvr-guided-tour-title-left'>Publish &amp; Embed</p>" +
                  "<div class='hotspot-instructions'>" +
                  "<p class='hotspot-intro'>Click <strong>Publish</strong> to make your tour live, then embed it anywhere:</p>" +
                  "<ol class='hotspot-steps'>" +
                  "<li>Shortcode</li>" +
                  "<li>WPVR Gutenberg block</li>" +
                  "<li><strong>Supported builders:</strong> Elementor, Divi, Oxygen, Visual Composer, Bricks</li>" +
                  "</ol></div>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#publish left',
            buttons: [ btnSkipTour(), btnSkipStep(), btnNext() ],
            when: {
                show: onShowWithCondition(function() {
                    // Check if the post status has changed to "publish"
                    var statusEl = document.getElementById('original_post_status');
                    if (statusEl && statusEl.value === 'publish') return true;

                    // Also check if the URL now has a post= param (meaning it was saved)
                    if (window.location.href.indexOf('post=') !== -1 &&
                        window.location.href.indexOf('action=edit') !== -1) return true;

                    return false;
                })
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // STEP 12 — Congratulations
        // ════════════════════════════════════════════════════════════════════
        main_tour.addStep('completed_guided_tour', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Congratulations! 🎉</p>" +
                  "<p class='wpvr-guided-tour-description'>Your first virtual tour is live and interactive. Visitors can now explore it in 360°!</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            attachTo: '#publish left',
            buttons: [ btnSkipTour(), btnDone() ],
            when: { show: onShow() }
        });

        // ── Scroll helper ────────────────────────────────────────────────────
        function scroll_to_popup(step) {
            main_tour.going_somewhere = false;
            if (!step) step = main_tour.getCurrentStep();
            var popup = $(step.el);
            $('body, html').animate({ scrollTop: popup.offset().top - 50 }, 500, function() {
                window.scrollTo(0, popup.offset().top - 50);
            });
        }

        // ── Start ────────────────────────────────────────────────────────────
        var isAutoStart = window.wpvr_new_user_tour && window.wpvr_new_user_tour.autoStart;
        if (isAutoStart || getParameterByName('wpvr-guide-tour') === '1') {
            main_tour.start();
        }

        main_tour.on('cancel', cancel_tour);
        main_tour.on('cancel',   function() { if (isAutoStart) wpvr_dismiss_new_user_tour(); });
        main_tour.on('complete', function() { if (isAutoStart) wpvr_dismiss_new_user_tour(); });

        function wpvr_dismiss_new_user_tour() {
            if (!window.wpvr_new_user_tour) return;
            $.post(window.wpvr_new_user_tour.ajaxUrl, {
                action: window.wpvr_new_user_tour.action,
                nonce:  window.wpvr_new_user_tour.nonce
            });
        }

        function cancel_tour() {
            main_tour.canceled = true;
            var param = getParameterByName('wpvr-guide-tour');
            if (param === '1') {
                var newUrl = updateParam('wpvr-guide-tour', 0);
                if (window.history && window.history.pushState) {
                    window.history.pushState({ path: newUrl }, '', newUrl);
                }
            }
        }

        function getParameterByName(name, url) {
            url  = url || window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var rx      = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
            var results = rx.exec(url);
            if (!results)    return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        function updateParam(name, value, url) {
            url = url || window.location.href;
            var u = new URL(url);
            u.searchParams.delete(name);
            return u.toString();
        }

    });

})( jQuery );