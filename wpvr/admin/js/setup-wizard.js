jQuery(document).ready(function($) {
    if (typeof window.LinnoOnboarding === 'undefined') {
        console.error('Linno Onboarding library not loaded');
        return;
    }

    const { registerOnboarding, engine, tracker } = window.LinnoOnboarding;
    
    const content = {
        'real-estate': {
            title: "Show your property without another site visit",
            sub: "Most agents publish their first property tour in minutes.",
            cta: "USE PROPERTY TEMPLATE",
            publishText: "PUBLISH PROPERTY TOUR",
            prev: "This is how buyers will experience your listing",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏠"
        },
        'hotel': {
            title: "Let guests experience your hotel before they book",
            sub: "Virtual tours help increase trust and direct bookings.",
            cta: "USE HOTEL TEMPLATE",
            publishText: "PUBLISH HOTEL TOUR",
            prev: "This is how guests will see your room",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏨"
        },
        'school': {
            title: "Showcase your campus to prospective students",
            sub: "Virtual campus tours help increase enrollment and engagement.",
            cta: "USE CAMPUS TEMPLATE",
            publishText: "PUBLISH CAMPUS TOUR",
            prev: "This is how students will explore your campus",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏫"
        },
        'automotive': {
            title: "Showcase your vehicles in stunning detail",
            sub: "Virtual tours help customers explore every angle before purchase.",
            cta: "USE AUTOMOTIVE TEMPLATE",
            publishText: "PUBLISH AUTOMOTIVE TOUR",
            prev: "This is how customers will explore your vehicles",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🚗"
        },
        'ecommerce': {
            title: "Create immersive shopping experiences",
            sub: "Virtual tours help customers visualize products in real spaces.",
            cta: "USE E-COMMERCE TEMPLATE",
            publishText: "PUBLISH E-COMMERCE TOUR",
            prev: "This is how customers will shop your products",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🛍"
        },
        'exhibitions': {
            title: "Bring your art and exhibitions to life",
            sub: "Virtual tours help visitors explore galleries from anywhere.",
            cta: "USE EXHIBITION TEMPLATE",
            publishText: "PUBLISH EXHIBITION TOUR",
            prev: "This is how visitors will experience your exhibition",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🎨"
        },
        'offices': {
            title: "Showcase your workspace to potential clients",
            sub: "Virtual office tours help build trust and attract talent.",
            cta: "USE OFFICE TEMPLATE",
            publishText: "PUBLISH OFFICE TOUR",
            prev: "This is how visitors will explore your office",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏢"
        },
        'training': {
            title: "Create engaging onboarding experiences",
            sub: "Virtual training tours help new employees learn faster.",
            cta: "USE TRAINING TEMPLATE",
            publishText: "PUBLISH TRAINING TOUR",
            prev: "This is how trainees will experience your program",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "📘"
        },
        'showrooms': {
            title: "Transform your showroom into a virtual experience",
            sub: "Virtual showrooms help customers explore products anytime.",
            cta: "USE SHOWROOM TEMPLATE",
            publishText: "PUBLISH SHOWROOM TOUR",
            prev: "This is how customers will browse your showroom",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏬"
        },
        'tourism': {
            title: "Let travelers explore destinations before they arrive",
            sub: "Virtual tours help increase bookings and visitor engagement.",
            cta: "USE TOURISM TEMPLATE",
            publishText: "PUBLISH TOURISM TOUR",
            prev: "This is how travelers will discover your destination",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏖"
        },
        'fitness': {
            title: "Showcase your fitness facilities",
            sub: "Virtual tours help attract new members and showcase amenities.",
            cta: "USE FITNESS TEMPLATE",
            publishText: "PUBLISH FITNESS TOUR",
            prev: "This is how members will explore your facility",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏋️"
        },
        'museums': {
            title: "Make cultural sites accessible to everyone",
            sub: "Virtual museum tours help reach global audiences.",
            cta: "USE MUSEUM TEMPLATE",
            publishText: "PUBLISH MUSEUM TOUR",
            prev: "This is how visitors will explore your museum",
            succ: "🎉 Your tour is published!",
            succSub: "Your virtual tour has been created and is ready to be displayed on your website.",
            icon: "🏛"
        }
    };

    let selectedIndustry = null;
    let uploadedImageUrl = null;
    let uploadedImageFile = null;
    let uploadedImageId = null; // WordPress attachment ID
    let panoramaViewer = null;
    let hotspots = [];
    let pendingHotspotCoords = null;
    let panoramaMouseDown = null;
    let panoramaIsDragging = false;
    let templateData = null; // Store template tour object
    let mediaUploader = null; // WordPress media uploader instance

    // Skip wizard and redirect to tour listing
    function skipWizard() {
        const listingUrl = ajaxurl.replace('admin-ajax.php', 'edit.php?post_type=wpvr_item');
        window.location.href = listingUrl;
    }

    // Generate random string for scene IDs (matching WPVR format)
    function generateRandomString(length) {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            var randomIndex = Math.floor(Math.random() * charactersLength);
            result += characters.charAt(randomIndex);
        }
        return result;
    }

    function initFileUpload() {
        const dropZone = $('#drop-zone');
        const fileInput = $('#image-upload');
        const dropZoneLink = $('.drop-zone-link');

        if (!dropZone.length || !fileInput.length || !dropZoneLink.length) {
            console.warn('Upload elements not found, retrying...');
            setTimeout(initFileUpload, 100);
            return;
        }

        dropZoneLink.off('click.upload');
        dropZone.off('click.upload');
        fileInput.off('change.upload input.upload');
        dropZone.off('dragover.upload dragenter.upload dragleave.upload drop.upload');

        // Click handler for the link specifically - open WordPress media library
        dropZoneLink.off('click.upload').on('click.upload', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // If media uploader already exists, open it
            if (mediaUploader) {
                mediaUploader.open();
                return false;
            }

            // Create WordPress media uploader
            mediaUploader = wp.media({
                title: 'Select or Upload 360° Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When image is selected
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                uploadedImageId = attachment.id;
                uploadedImageUrl = attachment.url;
                
                // Show preview
                $('#upload-preview-name').text(attachment.filename || 'Uploaded image');
                $('#uploaded-image-preview').attr('src', uploadedImageUrl);
                $('#drop-zone').hide();
                $('#upload-preview').show();
                
                // Get context from engine
                const currentStep = engine.getCurrentStep();
                if (currentStep) {
                    const stepContext = engine.getStepContext();
                    // Auto proceed to next step after a short delay
                    setTimeout(() => {
                        stepContext.goNext();
                    }, 500);
                }
            });

            // Open media uploader
            mediaUploader.open();
            
            return false;
        });

        // Click handler for the rest of the drop zone
        dropZone.off('click.upload').on('click.upload', function(e) {
            // Don't handle if clicking on the link (it has its own handler)
            if ($(e.target).closest('.drop-zone-link').length > 0) {
                return;
            }
            
            // Don't handle if clicking on buttons
            if ($(e.target).is('button') || $(e.target).closest('button').length > 0) {
                return;
            }
            
            // For other clicks on drop zone, trigger file input
            if (fileInput.length && fileInput[0]) {
                fileInput[0].click();
            }
        });

        // File input change - ensure it's properly bound
        fileInput.off('change.upload').on('change.upload', function(e) {
            const file = this.files && this.files[0];
            if (file) {
                handleFileUploadToWordPress(file);
            }
        });

        // Drag and drop
        dropZone.on('dragover.upload dragenter.upload', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });

        dropZone.on('dragleave.upload', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });

        dropZone.on('drop.upload', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileUploadToWordPress(files[0]);
            }
        });
    }

    function handleFileUploadToWordPress(file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPG, PNG, or WEBP)');
            return;
        }

        // Validate file size (max 50MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('File size must be less than 50MB');
            return;
        }

        uploadedImageFile = file;

        // Show loading state
        $('#drop-zone').hide();
        $('#upload-preview').show();
        $('#upload-preview-name').text('Uploading...');
        $('#uploaded-image-preview').attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM2YjcyODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5VcGxvYWRpbmcuLi48L3RleHQ+PC9zdmc+');

        // Create FormData for file upload
        const formData = new FormData();
        formData.append('action', 'wpvr_upload_image');
        formData.append('security', wpvrNonce);
        formData.append('image', file);

        // Upload to WordPress
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                // Upload progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        $('#upload-preview-name').text('Uploading... ' + Math.round(percentComplete) + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    uploadedImageId = response.data.attachment_id;
                    uploadedImageUrl = response.data.url;
                    
                    // Show preview
                    $('#upload-preview-name').text(file.name);
                    $('#uploaded-image-preview').attr('src', uploadedImageUrl);
                    
                    // Get context from engine
                    const currentStep = engine.getCurrentStep();
                    if (currentStep) {
                        const stepContext = engine.getStepContext();
                        // Auto proceed to next step after a short delay
                        setTimeout(() => {
                            stepContext.goNext();
                        }, 500);
                    }
                } else {
                    alert('Upload failed: ' + (response.data.message || 'Unknown error'));
                    $('#drop-zone').show();
                    $('#upload-preview').hide();
                }
            },
            error: function(xhr, status, error) {
                alert('Upload failed: ' + error);
                $('#drop-zone').show();
                $('#upload-preview').hide();
            }
        });
    }

    // Helper function to update dynamic content based on selected industry
    function updateDynamicContent() {
        const data = content[selectedIndustry];
        if(!data) return;

        $('.dynamic-title').text(data.title);
        $('.dynamic-sub').text(data.sub);
        $('#use-template-btn').text(data.cta);
        $('.dynamic-prev-title').text(data.prev);
        $('.dynamic-success-title').text(data.succ);
        $('.dynamic-success-sub').text(data.succSub);
        $('#publish-btn').text(data.publishText);
    }

    function updateIconPlaceholder() {
        const data = content[selectedIndustry];
        if(!data) return;
        
        $('#template-icon-placeholder').text(data.icon);
    }

    // Navigation helper function
    function navigateTo(stepId) {
        // Show/Hide Steps Container (contains sidebar + content)
        if (stepId === 'step-welcome') {
            $('#steps-container').hide();
            $('#welcome-content').show();
            $('#onboarding-app').removeClass('sidebar-visible');
        } else {
            $('#steps-container').css('display', 'flex');
            $('#welcome-content').hide();
            $('#onboarding-app').addClass('sidebar-visible');
        }

        // Toggle Active Step
        $('.step').removeClass('active');
        $('#' + stepId).addClass('active');

        // Update Sidebar Navigation UI
        $('.nav-item').removeClass('active');
        $(`.nav-item[data-step="${stepId}"]`).addClass('active');
        
        // Mark previous steps as completed
        updateNavProgress(stepId);

        // Trigger celebration effect on success step (only once)
        if (stepId === 'step-success' && !celebrationTriggered) {
            // Small delay to ensure step is fully rendered
            setTimeout(() => {
                triggerCelebration();
            }, 300);
        } else if (stepId !== 'step-success') {
            // Remove confetti if navigating away from success
            $('.confetti-container').remove();
            // Reset celebration trigger if going back (optional - comment out if you want it to only play once ever)
            // celebrationTriggered = false;
        }
    }

    function updateNavProgress(currentId) {
        // Logic to add completed class to sidebar items as they are finished
        const steps = ['step-vertical', 'step-template', 'step-preview', 'step-success'];
        const currentIndex = steps.indexOf(currentId);
        
        $('.nav-item').each(function() {
            const stepId = $(this).data('step');
            const stepIndex = steps.indexOf(stepId);
            
            if (stepIndex < currentIndex && stepIndex !== -1) {
                $(this).addClass('completed');
            } else {
                $(this).removeClass('completed');
            }
        });
    }

    // Track if celebration has been triggered to prevent replay
    let celebrationTriggered = false;

    function triggerCelebration() {
        // Check if already triggered
        if (celebrationTriggered) {
            return;
        }

        // Check for prefers-reduced-motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            return;
        }

        // Mark as triggered
        celebrationTriggered = true;

        // Remove any existing confetti
        $('.confetti-container').remove();

        // Create confetti container
        const confettiContainer = $('<div class="confetti-container"></div>');
        $('body').append(confettiContainer);

        // Create lightweight confetti particles (fewer particles for performance)
        const colors = ['#3F04FE', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
        const particleCount = 30; // Reduced from 50 for better performance
        
        // Get check circle position for burst origin
        const checkCircle = $('.check-circle');
        let centerX = window.innerWidth / 2;
        let centerY = window.innerHeight / 2;
        
        if (checkCircle.length) {
            const offset = checkCircle.offset();
            if (offset) {
                centerX = offset.left + checkCircle.outerWidth() / 2;
                centerY = offset.top + checkCircle.outerHeight() / 2;
            }
        }

        for (let i = 0; i < particleCount; i++) {
            const confetti = $('<div class="confetti"></div>');
            const color = colors[Math.floor(Math.random() * colors.length)];
            
            // Calculate random direction and distance
            const angle = (Math.PI * 2 * i) / particleCount + (Math.random() * 0.5);
            const distance = 100 + Math.random() * 150;
            const tx = Math.cos(angle) * distance;
            const ty = Math.sin(angle) * distance;
            const rotation = 360 + Math.random() * 360;

            confetti.css({
                'background': color,
                'left': centerX + 'px',
                'top': centerY + 'px',
                '--tx': tx + 'px',
                '--ty': ty + 'px',
                '--rot': rotation + 'deg',
                'width': (6 + Math.random() * 6) + 'px',
                'height': (6 + Math.random() * 6) + 'px',
                'border-radius': Math.random() > 0.5 ? '50%' : '0'
            });

            confettiContainer.append(confetti);
        }

        // Remove confetti after animation completes (1.2s duration)
        setTimeout(function() {
            confettiContainer.remove();
        }, 1500);
    }

    // Register onboarding with Linno Onboarding library
    registerOnboarding({
        plugin: 'wpvr-onboarding',
        version: '8.5.54',
        theme: {
            color: '#3F04FE'
        },
        // WordPress Telemetry Integration
        telemetry: {
            onSetupStarted: (data) => {
                console.log('📊 Telemetry: setup_started', data);
            },
            onSetupCompleted: (data) => {
                console.log('Setup completed event emitted by wizard flow');
            },
            onFirstStrikeCompleted: (data) => {
                console.log('First strike is tracked on first tour publish');
            }
        },
        steps: [
            {
                id: 'welcome',
                title: 'Welcome',
                description: 'Create your first virtual tour in under 10 minutes',
                canGoBack: false,
                canSkip: false,
                mount: (container, context) => {
                    navigateTo('step-welcome');                    
                    setTimeout(() => {
                        // Bind skip button
                        $('#skip-welcome').off('click').on('click', function(e) {
                            e.preventDefault();
                            skipWizard();
                        });
                        
                        $('#start-tour').off('click').on('click', function() {
                            const $startTourButton = $(this);
                            const consentChecked = $('#consent-checkbox').is(':checked');

                            if ($startTourButton.prop('disabled')) {
                                return;
                            }
                            
                            // If consent is given, save opt-in
                            if (consentChecked) {
                                $startTourButton.prop('disabled', true).addClass('is-loading');

                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'wpvr_save_opt_in_toggle',
                                        opt_in: consentChecked ? 1 : 0,
                                        security: wpvrNonce
                                    },
                                    success: function(response) {
                                        console.log('Opt-in saved:', response);
                                        context.goNext();
                                    },
                                    error: function(xhr, status, error) {
                                        console.log('Failed to save opt-in:', error);
                                        $startTourButton.prop('disabled', false).removeClass('is-loading');
                                        // Continue anyway
                                        context.goNext();
                                    }
                                });
                            } else {
                                context.goNext();
                            }
                        });
                    }, 0);
                }
            },
            {
                id: 'vertical',
                title: 'Industry',
                description: 'What are you creating a virtual tour for?',
                canGoBack: true,
                canSkip: false,
                mount: (container, context) => {
                    navigateTo('step-vertical');
                    
                    // Bind vertical selection
                    setTimeout(() => {
                        // Initially disable next button until industry is selected
                        const nextBtn = $('.next-btn[data-next="step-template"]');
                        if (!selectedIndustry) {
                            nextBtn.prop('disabled', true).addClass('disabled');
                        }

                        $('.v-card').off('click').on('click', function() {
                            $('.v-card').removeClass('active');
                            $(this).addClass('active');
                            selectedIndustry = $(this).data('vertical');
                            // Enable next button when industry is selected
                            nextBtn.prop('disabled', false).removeClass('disabled');
                            updateDynamicContent();
                            updateIconPlaceholder();
                        });

                        // Bind navigation buttons
                        $('.btn-back').off('click').on('click', () => {
                            context.goBack();
                        });

                        $('.next-btn[data-next="step-template"]').off('click').on('click', () => {
                            context.goNext();
                        });

                        // Bind skip button
                        $('#skip-vertical').off('click').on('click', function(e) {
                            e.preventDefault();
                            skipWizard();
                        });
                    }, 0);
                }
            },
            {
                id: 'template',
                title: 'Template',
                description: 'Choose your template',
                canGoBack: true,
                canSkip: false,
                onNext: (context) => {
                    // Validate that user has either uploaded an image or used a template
                    if (!uploadedImageUrl) {
                        alert('Please upload a 360° image or use a template before proceeding.');
                        return false;
                    }
                    return true;
                },
                mount: (container, context) => {
                    navigateTo('step-template');
                    updateDynamicContent();
                    updateIconPlaceholder();

                    setTimeout(() => {
                        // Show upload preview if image was already uploaded
                        if (uploadedImageUrl) {
                            $('#template-preview-box').hide();
                            $('#upload-link').hide();
                            $('#upload-section').show();
                            $('#drop-zone').hide();
                            $('#upload-preview').show();
                            $('#uploaded-image-preview').attr('src', uploadedImageUrl);
                            $('#upload-preview-name').text(uploadedImageFile ? uploadedImageFile.name : 'Uploaded image');
                        } else {
                            $('#template-preview-box').show();
                            $('#upload-link').show();
                            $('#upload-section').hide();
                        }

                        // Bind navigation buttons
                        $('.btn-back').off('click').on('click', () => {
                            context.goBack();
                        });

                        $('.next-btn[data-next="step-preview"]').not('#use-template-btn').off('click').on('click', () => {
                            context.goNext();
                        });

                        // Bind skip button
                        $('#skip-template').off('click').on('click', function(e) {
                            e.preventDefault();
                            skipWizard();
                        });

                        // Use template button handler - call remote API
                        $('#use-template-btn').off('click.template').on('click.template', function(e) {
                            e.preventDefault();
                            
                            // Show loading state
                            const $btn = $(this);
                            const originalText = $btn.text();
                            $btn.prop('disabled', true).addClass('is-loading').text('Importing template & media...');
                            
                            // Call AJAX to fetch template
                            $.ajax({
                                url: ajaxurl,
                                method: 'POST',
                                data: {
                                    action: 'wpvr_fetch_template',
                                    industry: selectedIndustry,
                                    security: wpvrNonce
                                },
                                success: function(response) {
                                    if (response.success && response.data.template) {
                                        templateData = response.data.template;

                                        if (templateData.post_id) {
                                            window.wpvrCreatedTourId = templateData.post_id;
                                            window.wpvrCreatedTourUrl = templateData.view_url || '';
                                            window.wpvrCreatedTourEditUrl = templateData.edit_url || '';
                                        }
                                        
                                        // If template has an image URL, use it
                                        if (templateData.image_url) {
                                            uploadedImageUrl = templateData.image_url;
                                        } else {
                                            // Fallback placeholder
                                            uploadedImageUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM2YjcyODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5UZW1wbGF0ZSBQcmV2aWV3PC90ZXh0Pjwvc3ZnPg==';
                                        }
                                                                                
                                        // Auto proceed to next step
                                        setTimeout(() => {
                                            context.goNext();
                                        }, 500);
                                    } else {
                                        alert('Failed to load template. Please try again.');
                                        $btn.prop('disabled', false).removeClass('is-loading').text(originalText);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Template fetch error:', error);
                                    alert('Failed to load template. Please try again.');
                                    $btn.prop('disabled', false).removeClass('is-loading').text(originalText);
                                }
                            });
                        });

                        // Upload link handler - show dropzone for upload
                        $('#upload-link').off('click').on('click', function(e) {
                            e.preventDefault();
                            
                            // Show upload section with dropzone
                            $('#template-preview-box').hide();
                            $('#upload-link').hide();
                            $('#upload-section').show();
                            $('#drop-zone').show();
                            $('#upload-preview').hide();
                            
                            // Initialize file upload handlers
                            initFileUpload();
                        });

                        // Use template link handler - go back to template preview
                        $('#use-template-link').off('click').on('click', function(e) {
                            e.preventDefault();
                            
                            // Show template preview and hide upload section
                            $('#template-preview-box').show();
                            $('#upload-link').show();
                            $('#upload-section').hide();
                        });

                        // Remove upload handler
                        $('#btn-remove-upload').off('click.upload').on('click.upload', function() {
                            uploadedImageUrl = null;
                            uploadedImageFile = null;
                            uploadedImageId = null;
                            $('#image-upload').val('');
                            $('#upload-preview').hide();
                            $('#drop-zone').show();
                            $('#template-preview-box').show();
                            $('#upload-section').hide();
                            $('#upload-link').show();
                        });

                        // Initialize file upload handlers when upload section is visible
                        // Don't initialize if section is hidden (elements might not be accessible)
                        if ($('#upload-section').is(':visible')) {
                            initFileUpload();
                        }
                    }, 0);
                }
            },
            {
                id: 'preview',
                title: 'Preview',
                description: 'This is how buyers will experience your listing',
                canGoBack: true,
                canSkip: false,
                onNext: (context) => {
                    // Validate that user has either uploaded an image or used a template
                    if (!uploadedImageUrl) {
                        alert('Please upload a 360° image or use a template before proceeding.');
                        return false;
                    }
                    return true;
                },
                mount: (container, context) => {
                    navigateTo('step-preview');
                    updateDynamicContent();

                    setTimeout(() => {
                        // Bind navigation buttons
                        $('.btn-back').off('click').on('click', () => {
                            context.goBack();
                        });

                        $('#publish-btn').off('click').on('click', function() {
                            const $btn = $(this);
                            const originalText = $btn.text();
                            $btn.prop('disabled', true).text('Publishing...');
                            
                            // Build panodata object matching WPVR format
                            // Generate scene ID (8 character random string)
                            const sceneId = generateRandomString(8);
                            
                            // Create scene object matching WPVR structure
                            const scene = {
                                'scene-type': 'equirectangular',
                                'scene-id': sceneId,
                                'hotspot-list': [],
                                'dscene': 'off',
                                'scene-attachment-url': uploadedImageUrl
                            };
                            
                            // Add hotspots if any (matching WPVR format)
                            if (hotspots.length > 0) {
                                hotspots.forEach(function(hotspot, index) {
                                    const hotspotId = generateRandomString(8);
                                    scene['hotspot-list'].push({
                                        'hotspot-title': hotspotId,
                                        'hotspot-pitch': hotspot.pitch,
                                        'hotspot-yaw': hotspot.yaw,
                                        'hotspot-customclass': '',
                                        'hotspot-scene': '',
                                        'hotspot-url': '',
                                        'hotspot-content': '<p>' + (hotspot.text || '') + '</p>',
                                        'hotspot-hover': '',
                                        'hotspot-type': hotspot.type || 'info',
                                        'hotspot-scene-list': 'none',
                                        'wpvr_url_open': {}
                                    });
                                });
                            }
                            
                            // Build panodata structure matching WPVR format
                            let panodata = {
                                'autoLoad': 1,
                                'showControls': 1,
                                'customcontrol': '',
                                'genericform': 'off',
                                'genericformshortcode': '',
                                'preview': '',
                                'defaultscene': sceneId, // Set default scene to generated scene ID
                                'scenefadeduration': '',
                                'panodata': {
                                    'scene-list': {
                                        '1': scene // WPVR uses numeric keys starting from 1
                                    }
                                },
                                'previewtext': 'Click To Load Panorama'
                            };
                            
                            // If template data exists, merge it with panodata
                            if (templateData && templateData.panodata) {
                                // Merge template settings
                                if (templateData.autoLoad !== undefined) panodata.autoLoad = templateData.autoLoad;
                                if (templateData.showControls !== undefined) panodata.showControls = templateData.showControls;
                                if (templateData.defaultscene) panodata.defaultscene = templateData.defaultscene;
                                
                                // Merge template panodata scene-list if exists
                                if (templateData.panodata && templateData.panodata['scene-list']) {
                                    // Merge template scenes with our scene
                                    Object.assign(panodata.panodata['scene-list'], templateData.panodata['scene-list']);
                                    // Ensure our scene is the first one (key 1)
                                    panodata.panodata['scene-list']['1'] = scene;
                                    panodata.defaultscene = sceneId;
                                }
                            }
                            
                            // Create tour via AJAX
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'wpvr_create_tour_from_wizard',
                                    panodata: JSON.stringify(panodata),
                                    templateMeta: templateData && templateData.meta ? JSON.stringify(templateData.meta) : '',
                                    existing_post_id: templateData && templateData.post_id ? templateData.post_id : '',
                                    title: 'My ' + (
                                        selectedIndustry === 'real-estate' ? 'Property' : 
                                        selectedIndustry === 'hotel' ? 'Hotel' : 
                                        selectedIndustry === 'school' ? 'Campus' :
                                        selectedIndustry === 'automotive' ? 'Automotive' :
                                        selectedIndustry === 'ecommerce' ? 'E-commerce' :
                                        selectedIndustry === 'exhibitions' ? 'Exhibition' :
                                        selectedIndustry === 'offices' ? 'Office' :
                                        selectedIndustry === 'training' ? 'Training' :
                                        selectedIndustry === 'showrooms' ? 'Showroom' :
                                        selectedIndustry === 'tourism' ? 'Tourism' :
                                        selectedIndustry === 'fitness' ? 'Fitness' :
                                        selectedIndustry === 'museums' ? 'Museum' : 'Virtual'
                                    ) + ' Tour',
                                    industry: selectedIndustry,
                                    security: wpvrNonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Store tour ID and URLs for later use
                                        window.wpvrCreatedTourId = response.data.post_id;
                                        window.wpvrCreatedTourUrl = response.data.view_url;
                                        window.wpvrCreatedTourEditUrl = response.data.edit_url;
                                        
                                        // Proceed to next step
                                        context.goNext();
                                    } else {
                                        alert('Failed to create tour: ' + (response.data.message || 'Unknown error'));
                                        $btn.prop('disabled', false).text(originalText);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Tour creation error:', error);
                                    alert('Failed to create tour. Please try again.');
                                    $btn.prop('disabled', false).text(originalText);
                                }
                            });
                        });

                        // Bind skip button
                        $('#skip-preview').off('click').on('click', function(e) {
                            e.preventDefault();
                            skipWizard();
                        });

                        // Initialize panorama viewer if image is uploaded
                        if (uploadedImageUrl) {
                            initPanoramaViewer();
                        } else {
                            $('#panorama-container').hide();
                            $('#preview-image-placeholder').show();
                        }
                    }, 0);
                }
            },
            {
                id: 'success',
                title: 'Publish',
                description: 'Your property tour is live!',
                canGoBack: true,
                canSkip: false,
                mount: (container, context) => {
                    navigateTo('step-success');
                    updateDynamicContent();

                    // Change pro badge color to #01B5FF on last step
                    setTimeout(() => {
                        $('.pro-badge').css({
                            'background': '#01B5FF',
                            'background-color': '#01B5FF'
                        });
                    }, 0);

                    // Automatically complete onboarding when user reaches the last step
                    setTimeout(() => {
                        // Complete the final step to trigger onboarding completion and firstStrike
                        context.completeStep();
                    }, 100);

                    setTimeout(() => {
                        // Clear localStorage
                        if (typeof Storage !== 'undefined') {
                            localStorage.clear();
                        }
                        
                        // Bind back button (restart)
                        $('.btn-back').off('click').on('click', () => {
                            location.reload();
                        });

                        // Update button text and show shortcode if tour is created
                        if (window.wpvrCreatedTourId) {                            
                            // Show shortcode instructions - insert before primary CTA
                            const shortcode = '[wpvr id="' + window.wpvrCreatedTourId + '"]';
                            const shortcodeHtml = '<div class="shortcode-instructions" style="margin: 30px 0; padding: 25px; background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%); border-radius: 12px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">' +
                                '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">' +
                                '<span style="font-size: 24px;">📋</span>' +
                                '<h4 style="margin: 0; font-size: 18px; font-weight: 600; color: #333;">Use This Shortcode on Your Site</h4>' +
                                '</div>' +
                                '<p style="margin: 0 0 18px 0; color: #666; font-size: 14px; line-height: 1.6;">Copy this shortcode and paste it into any page, post, or widget area to display your virtual tour:</p>' +
                                '<div class="shortcode-box" style="background: #fff; padding: 18px; border: 2px solid #3F04FE; border-radius: 8px; margin: 15px 0; display: flex; align-items: center; justify-content: space-between; gap: 15px; box-shadow: 0 2px 4px rgba(63,4,254,0.1);">' +
                                '<code id="tour-shortcode" style="font-size: 16px; color: #3F04FE; font-weight: 600; user-select: all; flex: 1; font-family: \'SF Mono\', Monaco, Consolas, monospace; letter-spacing: 0.5px;">' + shortcode + '</code>' +
                                '<button id="copy-shortcode-btn" style="padding: 12px 24px; background: #3F04FE; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; white-space: nowrap; transition: all 0.2s; box-shadow: 0 2px 4px rgba(63,4,254,0.3);">Copy Shortcode</button>' +
                                '</div>' +
                                '</div>';
                            
                            // Insert shortcode instructions before primary CTA container
                            if ($('.shortcode-instructions').length === 0) {
                                $('#step-success .primary-cta-container').before(shortcodeHtml);
                            }
                            
                            // Copy shortcode functionality
                            $('#copy-shortcode-btn').off('click').on('click', function() {
                                const shortcodeText = $('#tour-shortcode').text();
                                
                                // Modern clipboard API
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(shortcodeText).then(function() {
                                        const $btn = $(this);
                                        const originalText = $btn.text();
                                        $btn.text('Copied!').css('background', '#10B981');
                                        setTimeout(function() {
                                            $btn.text(originalText).css('background', '#3F04FE');
                                        }, 2000);
                                    }.bind(this));
                                } else {
                                    // Fallback for older browsers
                                    const tempInput = $('<input>');
                                    $('body').append(tempInput);
                                    tempInput.val(shortcodeText).select();
                                    document.execCommand('copy');
                                    tempInput.remove();
                                    
                                    const $btn = $(this);
                                    const originalText = $btn.text();
                                    $btn.text('Copied!').css('background', '#10B981');
                                    setTimeout(function() {
                                        $btn.text(originalText).css('background', '#3F04FE');
                                    }, 2000);
                                }
                            });
                        }

                        // Bind "Edit Your Tour" button - opens WP admin edit page
                        $('#edit-tour-btn').off('click.editTour').on('click.editTour', function(e) {
                            e.preventDefault();
                            
                            // Complete onboarding first
                            if (context && typeof context.completeStep === 'function') {
                                context.completeStep();
                            }
                            
                            // Navigate to edit tour page
                            if (window.wpvrCreatedTourId) {
                                const editUrl = ajaxurl.replace('admin-ajax.php', 'post.php?post=' + window.wpvrCreatedTourId + '&action=edit');
                                window.location.href = editUrl;
                            } else if (window.wpvrCreatedTourEditUrl) {
                                window.location.href = window.wpvrCreatedTourEditUrl;
                            }
                        });

                        // Bind skip button
                        $('#skip-success').off('click').on('click', function(e) {
                            e.preventDefault();
                            skipWizard();
                        });

                        // Bind footer "FINISH" button - go to tour listing
                        $('#step-success .btn-primary-outline').off('click.finish').on('click.finish', function(e) {
                            e.preventDefault();
                            
                            // Complete onboarding
                            if (context && typeof context.completeStep === 'function') {
                                context.completeStep();
                            }
                            
                            // Navigate to tour listing page
                            const listingUrl = ajaxurl.replace('admin-ajax.php', 'edit.php?post_type=wpvr_item');
                            window.location.href = listingUrl;
                        });
                    }, 0);
                }
            }
        ],
        firstStrike: {
            label: 'WP VR Onboarding Completed',
            verify: () => {
                console.log('First Strike verified! Onboarding completed.');
                return true;
            }
        }
    });

    // Start onboarding
    engine.start();

    // Listen to step changes and update UI
    tracker.on('step_changed', (data) => {
        const currentStep = engine.getCurrentStep();
        if (currentStep && currentStep.mount) {
            const container = document.getElementById('onboarding-app');
            if (container) {
                currentStep.mount(container, engine.getStepContext());
            }
        }
    });

    tracker.on('step_completed', (data) => {
        console.log('Step completed:', data);
    });

    tracker.on('onboarding_completed', (data) => {
        console.log('Onboarding completed!', data);
        // Celebration is already handled in the success step mount
        // Note: Setup completed is now handled in telemetry.onSetupCompleted callback
    });

    tracker.on('first_strike_verified', (data) => {
        console.log('First Strike verified!', data);
        // firstStrike verification is complete
    });

    // Initial render - show welcome step
    const currentStep = engine.getCurrentStep();
    if (currentStep && currentStep.mount) {
        const container = document.getElementById('onboarding-app');
        if (container) {
            currentStep.mount(container, engine.getStepContext());
        }
    }

    // Initialize Panorama Viewer
    function initPanoramaViewer() {
        // Hide placeholder, show panorama container
        $('#preview-image-placeholder').hide();
        $('#panorama-container').show();

        // Destroy existing viewer if any
        if (panoramaViewer) {
            try {
                panoramaViewer.destroy();
            } catch(e) {
                console.log('Viewer already destroyed');
            }
        }

        // Initialize pannellum viewer
        const config = {
            type: "equirectangular",
            panorama: uploadedImageUrl,
            autoLoad: true,
            showControls: true,
            hotSpotDebug: false,
            hotSpots: hotspots
        };

        panoramaViewer = pannellum.viewer('panorama', config);

        // Wait for viewer to be ready, then add click handler
        setTimeout(() => {
            // Add click handler for hotspot creation
            // Use pannellum's mouse event system
            const panoramaElement = document.getElementById('panorama');
            if (panoramaElement) {
                // Remove existing listeners
                panoramaElement.removeEventListener('mousedown', handlePanoramaMouseDown);
                panoramaElement.removeEventListener('mousemove', handlePanoramaMouseMove);
                panoramaElement.removeEventListener('mouseup', handlePanoramaMouseUp);
                
                // Add new listeners for click detection (not drag)
                panoramaElement.addEventListener('mousedown', handlePanoramaMouseDown);
                panoramaElement.addEventListener('mousemove', handlePanoramaMouseMove);
                panoramaElement.addEventListener('mouseup', handlePanoramaMouseUp);
            }
        }, 500);
    }

    // Handle panorama mousedown - track if it's a click or drag
    function handlePanoramaMouseDown(e) {
        // Only handle left clicks
        if (e.button !== 0) return;
        
        // Check if clicking on an existing hotspot
        if (e.target.closest('.pnlm-hotspot')) {
            return;
        }

        // Store mouse down position and time
        panoramaMouseDown = {
            x: e.clientX,
            y: e.clientY,
            time: Date.now()
        };
        panoramaIsDragging = false;
    }

    // Handle panorama mousemove - detect if dragging
    function handlePanoramaMouseMove(e) {
        if (panoramaMouseDown) {
            const deltaX = Math.abs(e.clientX - panoramaMouseDown.x);
            const deltaY = Math.abs(e.clientY - panoramaMouseDown.y);
            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
            
            // If mouse moved more than 5 pixels, it's a drag
            if (distance > 5) {
                panoramaIsDragging = true;
            }
        }
    }

    // Handle panorama mouseup - only show hotspot if it was a click, not a drag
    function handlePanoramaMouseUp(e) {
        if (!panoramaMouseDown) return;
        
        // Only handle left clicks
        if (e.button !== 0) {
            panoramaMouseDown = null;
            return;
        }

        // Check if clicking on an existing hotspot
        if (e.target.closest('.pnlm-hotspot')) {
            panoramaMouseDown = null;
            panoramaIsDragging = false;
            return;
        }

        // Only show hotspot modal if it was a click (not a drag)
        if (!panoramaIsDragging) {
            const timeDiff = Date.now() - panoramaMouseDown.time;
            // Also check time - if mouse was held for less than 300ms, it's likely a click
            if (timeDiff < 300) {
                // Get current pitch and yaw from viewer
                const pitch = panoramaViewer.getPitch();
                const yaw = panoramaViewer.getYaw();
                
                // Store coordinates for hotspot creation
                pendingHotspotCoords = { pitch, yaw };
                
                // Show hotspot modal
                showHotspotModal();
            }
        }

        // Reset tracking
        panoramaMouseDown = null;
        panoramaIsDragging = false;
    }

    // Show hotspot modal
    function showHotspotModal() {
        $('#hotspot-modal').fadeIn(200);
        $('#hotspot-text').val('').focus();
    }

    // Hide hotspot modal
    function hideHotspotModal() {
        $('#hotspot-modal').fadeOut(200);
        $('#hotspot-text').val('');
        pendingHotspotCoords = null;
    }

    // Save hotspot
    function saveHotspot() {
        const text = $('#hotspot-text').val().trim();
        
        if (!text) {
            alert('Please enter hotspot text');
            return;
        }

        if (!pendingHotspotCoords) {
            alert('Error: No coordinates available');
            return;
        }

        // Create hotspot object
        const hotspot = {
            pitch: pendingHotspotCoords.pitch,
            yaw: pendingHotspotCoords.yaw,
            type: "info",
            text: text
        };

        // Add to hotspots array
        hotspots.push(hotspot);

        // Reinitialize viewer with updated hotspots
        initPanoramaViewer();

        // Hide modal
        hideHotspotModal();
    }

    // Bind hotspot modal handlers
    $(document).ready(function() {
        $('#hotspot-save').off('click').on('click', saveHotspot);
        $('#hotspot-cancel, #hotspot-modal-close').off('click').on('click', hideHotspotModal);
        
        // Close on clicking outside modal
        $('#hotspot-modal').off('click').on('click', function(e) {
            if ($(e.target).is('#hotspot-modal')) {
                hideHotspotModal();
            }
        });
        
        // Prevent modal content clicks from closing modal
        $('.hotspot-modal-content').off('click').on('click', function(e) {
            e.stopPropagation();
        });
        
        // Close on Escape key
        $(document).off('keydown.hotspot').on('keydown.hotspot', function(e) {
            if (e.key === 'Escape' && $('#hotspot-modal').is(':visible')) {
                hideHotspotModal();
            }
        });

        // Submit on Enter key
        $('#hotspot-text').off('keypress').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                saveHotspot();
            }
        });
    });
});
