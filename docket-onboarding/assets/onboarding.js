/**
 * Docket Onboarding JavaScript
 * Version: 1.0.1
 * Updated: Avada form integration
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only run if we find the onboarding container
        if ($('.docket-onboarding').length === 0) {
            return;
        }

        // State management
        const state = {
            currentStep: 1,
            selectedPlan: '',
            selectedManagement: '',
            selectedBuildType: ''
        };

        // Initialize first step
        showStep(1);

        // Plan selection (Step 1)
        $('.docket-step1 .docket-plan-card').on('click', function(e) {
            e.preventDefault();
            const planType = $(this).hasClass('grow') ? 'grow' : 'pro';
            state.selectedPlan = planType;
            
            // Store plan type for styling
            $('.docket-onboarding').attr('data-selected-plan', planType);
            
            // Move to step 2
            showStep(2);
        });

        // Checklist functionality
        $('.docket-checklist-item').on('click', function() {
            $(this).toggleClass('checked');
            checkButtonStatus();
        });

        // Check if required checkboxes are checked (first 3 only)
        function checkButtonStatus() {
            const requiredCheckboxes = $('.docket-checklist-item').slice(0, 3); // First 3 checkboxes
            const checkedRequiredBoxes = requiredCheckboxes.filter('.checked').length;
            
            if (checkedRequiredBoxes === 3) {
                $('.docket-ready-btn').addClass('active');
                
                // Add pro styling if pro plan selected
                if (state.selectedPlan === 'pro') {
                    $('.docket-ready-btn').addClass('pro-style');
                }
            } else {
                $('.docket-ready-btn').removeClass('active pro-style');
            }
        }

        // Ready button (Step 2)
        $('.docket-ready-btn').on('click', function(e) {
            e.preventDefault();
            
            // Only proceed if button is active (all items checked)
            if ($(this).hasClass('active')) {
                showStep(3);
            } else {
                // Show a message or shake effect
                $(this).css('animation', 'shake 0.5s');
                setTimeout(() => {
                    $(this).css('animation', '');
                }, 500);
            }
        });

        // Management selection (Step 3)
        $('.docket-step3 .docket-plan-card').on('click', function(e) {
            e.preventDefault();
            const managementType = $(this).hasClass('self-managed') ? 'self' : 'vip';
            state.selectedManagement = managementType;
            
            if (managementType === 'vip') {
                // Handle VIP selection - you can customize this
                handleVIPSelection();
            } else {
                // Move to step 4 for self-managed
                showStep(4);
            }
        });

        // Build type selection (Step 4)
        $('.docket-step4 .docket-plan-card').on('click', function(e) {
            e.preventDefault();
            const buildType = $(this).hasClass('fast-build') ? 'fast' : 'standard';
            state.selectedBuildType = buildType;
            
            // Handle final selection
            handleFinalSelection();
        });

        // Back buttons
        $('.docket-back-btn').on('click', function(e) {
            e.preventDefault();
            const targetStep = $(this).data('target');
            showStep(targetStep);
        });

        // Show specific step
        function showStep(stepNumber) {
            state.currentStep = stepNumber;
            
            // Hide all steps
            $('.docket-step').removeClass('active');
            
            // Show target step
            $('.docket-step' + stepNumber).addClass('active');
            
            // Smooth scroll to top of container
            $('html, body').animate({
                scrollTop: $('.docket-onboarding').offset().top - 100
            }, 500);
            
            // Update progress if you add a progress bar later
            updateProgress(stepNumber);
        }

        // Update progress (for future progress bar implementation)
        function updateProgress(step) {
            const progress = (step / 4) * 100;
            // You can add a progress bar update here
            console.log('Progress:', progress + '%');
        }

        // Handle VIP selection
        function handleVIPSelection() {
            // Load the Website VIP form
            loadWebsiteVipForm();
        }

        // Handle final selection
        function handleFinalSelection() {
            // Load the appropriate form directly without confirmation
            if (state.selectedBuildType === 'fast') {
                loadFastBuildForm();
            } else {
                loadStandardBuildForm(); // Changed from alert
            }
        }

        // Load Fast Build Form
        function loadFastBuildForm() {
            showLoading();

            // Create form data to pass
            const formData = {
                action: 'docket_load_fast_build_form',
                nonce: docket_ajax.nonce,
                plan: state.selectedPlan,
                management: state.selectedManagement,
                buildType: state.selectedBuildType
            };

            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        // Add white background overlay
                        $('<div class="docket-white-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999;"></div>').appendTo('body');
                        
                        // Fade transition
                        $('.docket-onboarding').fadeOut(300, function() {
                            $(this).html(response.data.form_html).fadeIn(300, function() {
                                // Remove overlay after fade in
                                $('.docket-white-overlay').fadeOut(200, function() {
                                    $(this).remove();
                                });
                            });
                        });
                    } else {
                        showErrorMessage('Unable to load form. Please try again.');
                    }
                },
                error: function() {
                    hideLoading();
                    showErrorMessage('Connection error. Please try again.');
                }
            });
        }

        // Load Standard Build Form
        function loadStandardBuildForm() {
            showLoading();
            
            const formData = {
                action: 'docket_load_standard_build_form',
                nonce: docket_ajax.nonce,
                plan: state.selectedPlan,
                management: state.selectedManagement,
                buildType: state.selectedBuildType
            };
            
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Add white background overlay
                        $('<div class="docket-white-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999;"></div>').appendTo('body');
                        
                        // Fade transition
                        $('.docket-onboarding').fadeOut(300, function() {
                            $(this).html(response.data.form_html).fadeIn(300, function() {
                                // Remove overlay after fade in
                                $('.docket-white-overlay').fadeOut(200, function() {
                                    $(this).remove();
                                });
                            });
                        });
                    } else {
                        showErrorMessage('Unable to load form. Please try again.');
                    }
                },
                error: function() {
                    hideLoading();
                    showErrorMessage('Connection error. Please try again.');
                }
            });
        }

        // Load Website VIP Form
        function loadWebsiteVipForm() {
            showLoading();
            
            const formData = {
                action: 'docket_load_website_vip_form',
                nonce: docket_ajax.nonce,
                plan: state.selectedPlan,
                management: 'vip',
                buildType: state.selectedBuildType || 'standard'
            };
            
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Add white background overlay
                        $('<div class="docket-white-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999;"></div>').appendTo('body');
                        
                        // Fade transition
                        $('.docket-onboarding').fadeOut(300, function() {
                            // Insert HTML first
                            $(this).html(response.data.form_html);
                            
                            // Load CSS if provided
                            if (response.data.css_url && !$('link[href="' + response.data.css_url + '"]').length) {
                                $('<link>').attr({
                                    rel: 'stylesheet',
                                    type: 'text/css',
                                    href: response.data.css_url
                                }).appendTo('head');
                            }
                            
                            // Set AJAX URL if provided
                            if (response.data.ajax_url) {
                                window.ajaxurl = response.data.ajax_url;
                            }
                            
                            // Load JavaScript if provided
                            if (response.data.js_url) {
                                $.getScript(response.data.js_url)
                                    .done(function() {
                                        console.log('WebsiteVIP form JS loaded successfully');
                                    })
                                    .fail(function() {
                                        console.error('Failed to load WebsiteVIP form JavaScript');
                                    });
                            }
                            
                            // Fade in
                            $(this).fadeIn(300, function() {
                                // Remove overlay after fade in
                                $('.docket-white-overlay').fadeOut(200, function() {
                                    $(this).remove();
                                });
                            });
                        });
                    } else {
                        showErrorMessage('Unable to load form. Please try again.');
                    }
                },
                error: function() {
                    hideLoading();
                    showErrorMessage('Connection error. Please try again.');
                }
            });
        }

        // Embed Avada form
        function embedAvadaForm(formId) {
            // Show loading state
            showLoading();

            // Create a new step for the form
            const formStepHtml = `
                <div class="docket-step docket-step5" data-step="5">
                    <div class="docket-nav-container">
                        <a href="#" class="docket-back-btn" data-target="4">← Back</a>
                        <div class="docket-progress-container">
                            <div class="docket-progress-bar">
                                <div class="docket-progress-fill" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="docket-content">
                        <div class="docket-header">
                            <h2>Complete Your Order</h2>
                            <p>You're almost done! Please fill out the form below to complete your order.</p>
                        </div>
                        <div class="docket-form-container">
                            <div class="docket-form-loading">
                                <div class="docket-spinner"></div>
                                <p>Loading form...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add the form step if it doesn't exist
            if ($('.docket-step5').length === 0) {
                $('.docket-onboarding').append(formStepHtml);
            }

            // Show the form step
            $('.docket-step').removeClass('active');
            $('.docket-step5').addClass('active');

            // Prepare form data to pass
            const formData = {
                plan: state.selectedPlan,
                management: state.selectedManagement,
                buildType: state.selectedBuildType,
                buildSpeed: state.selectedBuildType === 'fast' ? '3 days' : '21-30 days'
            };

            // Load the Avada form via AJAX
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'docket_load_avada_form',
                    nonce: docket_ajax.nonce,
                    form_id: formId,
                    form_data: formData
                },
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        $('.docket-form-container').html(response.data.form_html);
                        
                        // Pre-fill form fields with our data
                        prefillAvadaForm(formData);
                        
                        // Clean up Avada form styling
                        cleanupAvadaForm();
                        
                        // Re-initialize Avada form scripts if needed
                        if (typeof fusion !== 'undefined' && fusion.forms) {
                            fusion.forms.reinitialize();
                        }
                        
                        // Ensure Avada form JavaScript is working
                        if (typeof jQuery !== 'undefined') {
                            jQuery(document).trigger('fusion-form-loaded');
                            
                            // Re-bind any form events that might have been lost
                            setTimeout(function() {
                                if (window.avadaForms) {
                                    window.avadaForms.reinit();
                                }
                            }, 100);
                        }
                    } else {
                        $('.docket-form-container').html(
                            '<div class="docket-error-message">' +
                            '<p>Unable to load the form. Please try again or contact support.</p>' +
                            '<button class="docket-retry-btn">Try Again</button>' +
                            '</div>'
                        );
                    }
                },
                error: function() {
                    hideLoading();
                    $('.docket-form-container').html(
                        '<div class="docket-error-message">' +
                        '<p>Connection error. Please try again.</p>' +
                        '<button class="docket-retry-btn">Retry</button>' +
                        '</div>'
                    );
                }
            });

            // Handle retry button
            $(document).on('click', '.docket-retry-btn', function() {
                embedAvadaForm(formId);
            });

            // Handle back button for step 5
            $(document).on('click', '.docket-step5 .docket-back-btn', function(e) {
                e.preventDefault();
                showStep(4);
            });
        }

        // Redirect to form page
        function redirectToFormPage(formId) {
            // Prepare data to pass via URL parameters
            const params = new URLSearchParams({
                plan: state.selectedPlan,
                management: state.selectedManagement,
                build_type: state.selectedBuildType,
                form_id: formId
            });

            // You'll need to set up these pages with the embedded forms
            const redirectUrl = formId === config.formIds.fast 
                ? `/fast-build-order?${params}` 
                : `/standard-build-order?${params}`;

            // Show loading state briefly
            showLoading();

            // Store data in session for the form page to retrieve
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'docket_store_form_data',
                    nonce: docket_ajax.nonce,
                    form_data: state
                },
                success: function() {
                    window.location.href = redirectUrl;
                },
                error: function() {
                    hideLoading();
                    alert('Error preparing form. Please try again.');
                }
            });
        }

        // Pre-fill Avada form fields
        function prefillAvadaForm(data) {
            // Try to find and fill hidden fields or visible fields
            // Adjust these selectors based on your actual Avada form field IDs/names
            
            // Example field mappings - you'll need to adjust these to match your form
            if ($('input[name="plan_type"]').length) {
                $('input[name="plan_type"]').val(data.plan);
            }
            
            if ($('input[name="management_type"]').length) {
                $('input[name="management_type"]').val(data.management);
            }
            
            if ($('input[name="build_speed"]').length) {
                $('input[name="build_speed"]').val(data.buildSpeed);
            }
            
            // You might also have hidden fields with specific IDs
            $('#docket_plan').val(data.plan);
            $('#docket_management').val(data.management);
            $('#docket_build_type').val(data.buildType);
            
            // If using select dropdowns
            $('select[name="plan_selection"]').val(data.plan);
            $('select[name="build_type_selection"]').val(data.buildType);
        }

        // Handle form submission (for VIP or other cases)
        function handleFormSubmission(type) {
            // Prepare data
            const formData = {
                action: 'docket_onboarding_submit',
                nonce: docket_ajax.nonce,
                plan: state.selectedPlan,
                management: state.selectedManagement,
                buildType: state.selectedBuildType,
                submissionType: type
            };

            // Show loading state
            showLoading();

            // AJAX submission
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    
                    if (response.success) {
                        // Handle successful submission
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        } else {
                            showSuccessMessage(response.data.message);
                        }
                    } else {
                        showErrorMessage(response.data.message || 'An error occurred. Please try again.');
                    }
                },
                error: function() {
                    hideLoading();
                    showErrorMessage('Connection error. Please try again.');
                }
            });
        }

        // Loading state
        function showLoading() {
            $('.docket-onboarding').addClass('loading');
            if ($('.docket-loading-overlay').length === 0) {
                $('<div class="docket-loading-overlay"><div class="docket-spinner"></div></div>').appendTo('.docket-onboarding');
            }
        }

        function hideLoading() {
            $('.docket-onboarding').removeClass('loading');
            $('.docket-loading-overlay').remove();
        }

        // Success message
        function showSuccessMessage(message) {
            const successHtml = `
                <div class="docket-success-message">
                    <div class="docket-success-icon">✓</div>
                    <h3>Success!</h3>
                    <p>${message}</p>
                </div>
            `;
            
            $('.docket-onboarding').html(successHtml);
        }

        // Error message
        function showErrorMessage(message) {
            const errorHtml = `
                <div class="docket-error-message">
                    <p>${message}</p>
                    <button class="docket-retry-btn">Try Again</button>
                </div>
            `;
            
            // Insert error at top of current step
            $('.docket-step.active').prepend(errorHtml);
            
            // Retry button
            $('.docket-retry-btn').on('click', function() {
                $('.docket-error-message').remove();
            });
        }

        // Clean up Avada form styling
        function cleanupAvadaForm() {
            // Remove any inline styles that might conflict
            $('.docket-form-container .fusion-form-wrapper').removeAttr('style');
            $('.docket-form-container .fusion-form').removeAttr('style');
            
            // Add custom classes for better targeting if needed
            $('.docket-form-container .fusion-form').addClass('docket-styled-form');
            
            // Ensure radio/checkbox labels are clickable
            $('.docket-form-container .fusion-form-radio label, .docket-form-container .fusion-form-checkbox label').css('cursor', 'pointer');
            
            // Fix any layout issues
            $('.docket-form-container .fusion-form-field').each(function() {
                $(this).find('> div').css('width', '100%');
            });
        }

        // Optional: Save progress to localStorage for users who leave and come back
        function saveProgress() {
            const progress = {
                step: state.currentStep,
                plan: state.selectedPlan,
                management: state.selectedManagement,
                buildType: state.selectedBuildType,
                timestamp: Date.now()
            };
            
            // Save to localStorage
            localStorage.setItem('docket_onboarding_progress', JSON.stringify(progress));
        }

        // Optional: Restore progress
        function restoreProgress() {
            const saved = localStorage.getItem('docket_onboarding_progress');
            
            if (saved) {
                const progress = JSON.parse(saved);
                
                // Check if saved progress is less than 24 hours old
                const dayAgo = Date.now() - (24 * 60 * 60 * 1000);
                
                if (progress.timestamp > dayAgo) {
                    // Restore state
                    state.currentStep = progress.step || 1;
                    state.selectedPlan = progress.plan || '';
                    state.selectedManagement = progress.management || '';
                    state.selectedBuildType = progress.buildType || '';
                    
                    // Show appropriate step
                    if (state.currentStep > 1 && confirm('Would you like to continue where you left off?')) {
                        showStep(state.currentStep);
                        if (state.selectedPlan) {
                            $('.docket-onboarding').attr('data-selected-plan', state.selectedPlan);
                        }
                    }
                }
            }
        }

        // Initialize progress restoration
        restoreProgress();

        // Save progress on step change
        $(window).on('beforeunload', function() {
            if (state.currentStep > 1) {
                saveProgress();
            }
        });

    });

})(jQuery);
