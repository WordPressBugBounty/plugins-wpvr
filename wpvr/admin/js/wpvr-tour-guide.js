(function( $ ) {
    'use strict';

    $(document).ready(function(){

        var main_tour = new Shepherd.Tour();
       var guide_tranlation = window.wpvr_tour_guide_obj.Tour_Guide_Translation

        // Inject Shepherd step 1 background CSS
        var shepherdStep1Bg = document.createElement('style');
        shepherdStep1Bg.innerHTML = `
       .shepherd-content {
            border-radius: 10px !important;
            background: #F3F0FF !important;
        }
        .shepherd-background-step-1 .shepherd-content {
            background-image: url('` + window.wpvr_tour_guide_obj.step1_bg_image + `') !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        p.wpvr-guided-tour-title {
            font-weight: 600;
            font-size: 20px;
            line-height: 1.2;
            letter-spacing: 0.2px;
            text-align: center;
            color: #3F04FE;
        }


        p.wpvr-guided-tour-title-left{
            font-weight: 600;
            font-size: 20px;
            line-height: 1.2;
            letter-spacing: 0.2px;
            color: #3F04FE;
            text-align: left;
        }

        p.wpvr-guided-tour-description {
            font-weight: 400;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.2px;
            text-align: center;
            color: #666666;
        }

        p.wpvr-guided-tour-description span {
            font-family: Roboto;
            font-weight: 700;
            font-style: Bold;
            font-size: 14px;
            leading-trim: NONE;
            line-height: 21px;
            letter-spacing: 0.2px;
            text-align: center;
            color: #666666;
        }

          .shepherd-progress-container {
            width: 100%;
            height: 4px;
            background-color: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .shepherd-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4169e1, #5c7cfa);
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .shepherd-buttons {
            display: flex;
            align-items: center;
            justify-content: space-between;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .shepherd-buttons li {
            flex: 0 0 auto;
        }
        
        .shepherd-step-counter {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            padding: 0 15px;
        }

          a.shepherd-button.udp-tour-end {
            width: 81px;
            height: 32px;
            border-radius: 5px;
            border: 1px solid #333333;
            gap: 10px;
            font-weight: 400;
            font-size: 14px;
            line-height: 1.6;
            letter-spacing: 0.2px;
            text-align: center;
            color: #333333 !important;
            background: #FFFFFF !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer !important;
            text-decoration: none !important;
        }
        
        /* Remove the cross icon */
        a.shepherd-button.udp-tour-end::before {
            display: none !important;
            content: none !important;
        }
        
        a.shepherd-button.udp-tour-end:hover {
            background: #f5f5f5;
        }

        /* Next button with arrow */
        a.shepherd-button.button.button-primary {
            display: inline-flex !important;
            align-items: center;
            margin-left: 8px;
            width: 81px !important;
            height: 32px !important;
            border-radius: 5px;
            font-weight: 400;
            font-size: 14px;
            line-height: 1.6;
            letter-spacing: 0.2px;
            justify-content: center !important;
        }
        
        .arrow-icon {
            display: inline-flex;
            align-items: center;
            width: 12px;
            height: 12px;
            margin-left: 4px;
        }
        
        .arrow-icon svg {
            width: 100%;
            height: 100%;
            display: block;
        }

          .hotspot-instructions {
            text-align: left;
            line-height: 1.6;
        }
        
        .hotspot-intro {
            color: #666;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .how-to-section {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .how-to-section strong {
            color: #333;
        }
        
        .help-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px !important;
            height: 14px !important;
            background-color: #3F04FE;
            color: white !important;
            border-radius: 50%;
            font-size: 12px !important;
            font-weight: bold !important;
            cursor: help;
        }

        `;
        document.head.appendChild(shepherdStep1Bg);
        main_tour.options.defaults = {
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour shadow-md bg-purple-dark',
            showCancelLink: true,
            useModalOverlay: true,
            scrollTo: true,
            tetherOptions: {
                constraints: [
                    {
                        to: 'scrollParent',
                        attachment: 'together',
                        pin: false
                    }
                ]
            }
        };
        var next_button_text = guide_tranlation.next_button_text;
        var back_button_text = guide_tranlation.previous_button_text;

        //Start Sences guide tour

        main_tour.addStep('tour_title', {
            title:'',
            text: "<p class='wpvr-guided-tour-title'>Name Your Tour</p><p class='wpvr-guided-tour-description'>Give your virtual tour a memorable title. This helps you find it later in your tour list.</p>",
            attachTo: '#post-body-content bottom',
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: guide_tranlation.end_tour,
                    action: main_tour.cancel
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: function() {
                        const titleInput = document.getElementById('title');
                        const titleValue = titleInput ? titleInput.value.trim() : '';
                        if (titleValue.length === 0) {
                            alert('Please enter a title for your tour to proceed.');
                        } else {;
                            main_tour.next();
                        }
                    }
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    addProgressToFooter(this.el, 1 , main_tour.steps.length);
                }
            }
        })

        function addProgressToFooter(stepElement, currentStep, totalSteps) {
            const footer = stepElement.querySelector('footer');
            if (!footer) return;
            
            const buttonsList = footer.querySelector('.shepherd-buttons');
            if (!buttonsList) return;
            
            // Remove existing progress elements if any
            const existingProgress = footer.querySelector('.shepherd-progress-container');
            const existingCounter = footer.querySelector('.shepherd-step-counter');
            if (existingProgress) existingProgress.remove();
            if (existingCounter) existingCounter.remove();
            
            // Create progress bar
            const progressContainer = document.createElement('div');
            progressContainer.className = 'shepherd-progress-container';
            
            const progressBar = document.createElement('div');
            progressBar.className = 'shepherd-progress-bar';
            const progress = ((currentStep) / totalSteps) * 100;
            progressBar.style.width = `${progress}%`;
            
            progressContainer.appendChild(progressBar);
            
            // Create step counter
            const stepCounter = document.createElement('div');
            stepCounter.className = 'shepherd-step-counter';
            stepCounter.textContent = `${currentStep}/${totalSteps}`;
            
            // Insert progress bar before buttons
            footer.insertBefore(progressContainer, buttonsList);
            
            // Insert step counter inside buttons list between buttons
            const buttons = buttonsList.querySelectorAll('li');
            if (buttons.length > 0) {
                const counterLi = document.createElement('li');
                counterLi.appendChild(stepCounter);
                
                // Insert between first and last button (or after first if only one button)
                if (buttons.length > 1) {
                buttonsList.insertBefore(counterLi, buttons[buttons.length - 1]);
                } else {
                buttonsList.appendChild(counterLi);
                }
            }

              const nextButton = buttonsList.querySelector('.button-primary');
                if (nextButton && !nextButton.querySelector('.arrow-icon')) {
                    const arrowIcon = document.createElement('span');
                    arrowIcon.className = 'arrow-icon';
                    arrowIcon.innerHTML = `<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.03027 8.19L10.7502 4.46997L7.03027 0.75" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M0.75 4.47021H10.75" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    `;
                    nextButton.appendChild(arrowIcon);
                }
        }


        main_tour.addStep('tour_title_given', {
            title:'',
            text: "<p class='wpvr-guided-tour-title'>Great!</p><p class='wpvr-guided-tour-description'>Your tour has a name. You\’re ready to start building your first scene!</p>",
            attachTo: '#post-body-content bottom',
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next,
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        })

        main_tour.addStep('upload_image', {
            title:  '',
            text: "<p class='wpvr-guided-tour-title'>Add Your First Scene</p><p class='wpvr-guided-tour-description'>Upload your first panorama image. Don\’t worry about Scene IDs — they are auto-generated for you but can be edited if you want a custom name (like S1, Scene1, or 01). <span class='help-icon'>?</span></p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '.single-scene.rex-pano-tab.active .scene-upload right',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: function() {
                        // Check if image URL exists in the hidden input
                        const imageUrlInput = document.querySelector('.single-scene.rex-pano-tab.active .scene-attachment-url');
                        
                        if (!imageUrlInput || !imageUrlInput.value || imageUrlInput.value.trim() === '') {
                            // Hide the shepherd tour
                            const shepherdElement = document.querySelector('.shepherd-element');
                            if (shepherdElement) {
                                shepherdElement.style.display = 'none';
                            }
                            
                            // Show alert
                            alert('Please upload an image before proceeding to the next step.');
                            
                            // Show the shepherd tour again after alert is dismissed
                            if (shepherdElement) {
                                shepherdElement.style.display = 'block';
                            }
                            
                            return false;
                        }
                        
                        // Proceed to next step if validation passes

                        main_tour.next();
                    }
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        $('.single-scene.rex-pano-tab.active .scene-upload').on('click',function(event){
            main_tour.hide()
            if($(this).parent().find('.wpvr_continue_guide').length == 0 && !main_tour.canceled ){
                $(this).parent().append('<span class="wpvr_continue_guide" >Continue to guide</span>');
            }
        })

        $(document).on('click', "span.wpvr_continue_guide", function() {
            $(this).remove();
            main_tour.show("upload_image_complete")
            $('body').addClass('shepherd-active')
        });




        main_tour.addStep('upload_image_complete', {
            title: '',
            text: "<p class='wpvr-guided-tour-title'>Awesome!</p><p class='wpvr-guided-tour-description'>Your first scene is live. Let’s preview it in 360° mode!</p>",
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            attachTo: '.single-scene.rex-pano-tab.active .scene-upload right',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });
        main_tour.addStep('preview_tour_section', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Preview Your Panorama</p><p class='wpvr-guided-tour-description'>Click <span>Preview</span> to explore your scene in virtual tour mode.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#wpvr_item_builder__box left',

            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });


        main_tour.addStep('add_hotspot_section', {
            title: '',
            text: `<p class='wpvr-guided-tour-title-left'>Add Your First Hotspot</p>    <div class="hotspot-instructions">
      <p class="hotspot-intro">Hotspots make your tour interactive. They can show text, images, videos, or links.</p>
      
      <div class="how-to-section">
        <strong>How to add a hotspot:</strong>
        <span class="help-icon">?</span>
      </div>
      
      <ol class="hotspot-steps">
        <li>
          <strong>Drag to location:</strong> In the Preview, drag to where you want the hotspot.
        </li>
        <li>
          <strong>Set content:</strong> Add the content your viewers will see when they click it.
        </li>
        <li>
          <strong>Save:</strong> Click Update to lock it in.
        </li>
      </ol>
    </div>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#wpvr_item_builder__box left',

            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: function(){
                        main_tour.next()
                    }
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        main_tour.addStep('choose_preview', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Set Your Hotspot Location</p><p class='wpvr-guided-tour-description'>In this Preview, drag to your desired location and click on it, exactly where you want to set the hotspot.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#wpvr_item_builder__box left',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                    setTimeout(function() {
                        const hotspotButton = document.querySelector(".rex-pano-nav-menu.main-nav ul li.hotspot span");
                        if (hotspotButton) {
                            hotspotButton.click();
                        }
                    }, 100);
                }
            }
        });

        main_tour.addStep('assigin_pitch_yaw', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Assign Coordinates</p><p class='wpvr-guided-tour-description'>Once you see the <span>Pitch & Yaw</span> value for the spot, click on this Arrow. It'll be set as the coordinate for your hotspot.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#panodata  right',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        main_tour.addStep('pitch_yaw_set', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Pitch Coordinate Set</p><p class='wpvr-guided-tour-description'>Here you can see the <span>Pitch</span> value has been set for the hotspot.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#scene-1-hotspot-1 .hotspot-pitch  right',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        main_tour.addStep('pitch_yaw_set_2', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Yaw Coordinate Set</p><p class='wpvr-guided-tour-description'>Here you can see the <span>Yaw</span> value has been set for the hotspot.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#scene-1-hotspot-1 .hotspot-yaw  right',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        main_tour.addStep('on_click_content_info', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Set Click Content</p><p class='wpvr-guided-tour-description'>Here, you can set what content your viewer will see after clicking on the Hotspot. You can either set a URL or any other content using this editor.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#scene-1-hotspot-1 .hotspot-type.hotspot-setting:not(.hotspot-hover)  top',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        main_tour.addStep('on_hover_info', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Set Hover Content</p><p class='wpvr-guided-tour-description'>Here, you can set what content your viewer will see when hovering over the Hotspot. You can set a tooltip or preview text using this editor.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#scene-1-hotspot-1 .hotspot-hover  top',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });
        main_tour.addStep('hotspot_preview_section', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Preview Your Hotspots</p><p class='wpvr-guided-tour-description'>Click Preview to see how your hotspot looks and behaves. Adjust content or placement as needed.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#wpvr_item_builder__box left',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: function(){
                        main_tour.next()
                        $(".rex-pano-nav-menu.main-nav ul li.hotspot span").trigger('click')
                    }
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });


         main_tour.addStep('add_hotspot_section_completed', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Nice!</p><p class='wpvr-guided-tour-description'>You just added your first interactive element. Your tour is engaging now.</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            attachTo: '#wpvr_item_builder__box left',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: function(){
                        main_tour.next()
                    }
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });


        main_tour.addStep('publish_tour', {
            title: '',
            text: `<p class='wpvr-guided-tour-title-left'>Publish & Embed</p><div class="hotspot-instructions">
            <p class="hotspot-intro">Click Publish to make your tour live. Then embed it anywhere on your site:</p>
            <ol class="hotspot-steps">
                <li>
                    <strong>Shortcode.</strong>
                </li>
                <li>
                    <strong>WPVR Gutenberg block.</strong>   
                </li>
                <li>
                    <strong>Supported builders:</strong> Elementor, Divi, Oxygen, Visual Composer, Bricks
                </li>
            </ol>
            </div>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index',
            attachTo: '#publish  left',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: next_button_text,
                    action: main_tour.next
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });

        main_tour.addStep('completed_guided_tour', {
            title: '',
            text: `<p class='wpvr-guided-tour-title'>Congratulations!</p><p class='wpvr-guided-tour-description'>Your first virtual tour is live and interactive. Visitors can now explore it in 360°!</p>`,
            classes: 'shepherd-theme-arrows-plain-buttons shepherd-main-tour super-index shepherd-background-step-1',
            attachTo: '#publish  left',
            buttons: [
                {
                    classes: 'udp-tour-end',
                    text: back_button_text,
                    action: function() {
                        main_tour.back();
                    }
                },
                {
                    classes: 'button button-primary',
                    text: guide_tranlation.done_text,
                    action: function(){
                        main_tour.complete()
                    }
                }
            ],
            when: {
                show: function() {
                    scroll_to_popup();
                    const currentStepIndex = main_tour.steps.indexOf(main_tour.getCurrentStep()) + 1;   
                    addProgressToFooter(this.el, currentStepIndex, main_tour.steps.length);
                }
            }
        });


        //End Hotspot
        /**
         * Scroll to Popup
         *
         * @param {Object} step
         */
        var scroll_to_popup = function(step) {
            main_tour.going_somewhere = false;
            if (!step) {
                step = main_tour.getCurrentStep();
            }
            var popup = $(step.el);
            var target = $(step.tether.target);
            $('body, html').animate({
                scrollTop: popup.offset().top - 50
            }, 500, function() {
                window.scrollTo(0, popup.offset().top - 50);
            });

        }
        main_tour.start();
        main_tour.on('cancel', cancel_tour);

        /**
         * Cancel tour
         */
        function cancel_tour() {
            // The tour is either finished or [x] was clicked
            main_tour.canceled = true;
           var get_param =  getParameterByName("wpvr-guide-tour");
           if(get_param == "1"){
               var newUrl = updateParam("wpvr-guide-tour",0);
               if (window.history != 'undefined' && window.history.pushState != 'undefined') {
                   window.history.pushState({ path: newUrl }, '', newUrl);
               }
           }
        };

        /**
         * Get URL parameter By name
         */
        function getParameterByName(name, url = window.location.href) {
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }
        /**
         * Delete parameter By name
         */
        function updateParam (name,value, url = window.location.href){
            var url = new URL(url);
            var search_params = url.searchParams;
            search_params.delete(name);

            url.search = search_params.toString();

            var new_url = url.toString();

            return new_url;
        }

    })

})( jQuery );