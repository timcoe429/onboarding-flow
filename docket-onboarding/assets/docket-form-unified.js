/**
 * Unified Form JavaScript
 * Single initialization function that works for all form types
 * Accepts form configuration from PHP
 */
(function($) {
    /**
     * Initialize a multi-step form based on configuration
     * 
     * @param {Object} config Form configuration object
     * @param {string} config.formId - Form ID selector (e.g., '#fastBuildForm')
     * @param {string} config.formType - Form type for session storage (e.g., 'fast-build')
     * @param {string} config.actionName - AJAX action name (e.g., 'docket_submit_fast_build_form')
     * @param {number} config.stepCount - Total number of steps
     */
    function initDocketForm(config) {
        // Default configuration
        const formConfig = {
            formId: config.formId || '#fastBuildForm',
            formType: config.formType || 'fast-build',
            actionName: config.actionName || 'docket_submit_fast_build_form',
            stepCount: config.stepCount || 8
        };

        const form = $(formConfig.formId);
        const steps = form.find('.form-step');
        const progressFill = $('.docket-progress-fill');
        const progressDots = $('.docket-progress-dots span');

        // Safeguard: If form elements don't exist, wait and retry
        if (form.length === 0 || steps.length === 0) {
            setTimeout(function() {
                initDocketForm(formConfig);
            }, 100);
            return;
        }

        // Load saved step from sessionStorage or start at 1
        const storageKey = formConfig.formType + 'Step';
        let currentStep = parseInt(sessionStorage.getItem(storageKey)) || 1;

        // --- NAVIGATION & SUBMISSION ---
        // Use delegated event handlers attached to the form itself
        form.on('click', '.btn-next', function(e) {
            e.preventDefault();
            
            // Validate current step before advancing
            if (!validateStep(currentStep)) {
                return false;
            }
            
            if (currentStep < steps.length) {
                currentStep++;
                sessionStorage.setItem(storageKey, currentStep);
                showStep(currentStep);
            }
        });

        form.on('click', '.btn-prev', function(e) {
            e.preventDefault();
            if (currentStep > 1) {
                currentStep--;
                sessionStorage.setItem(storageKey, currentStep);
                showStep(currentStep);
            }
        });

        // Handle submit button click
        form.on('click', '.btn-submit', function(e) {
            e.preventDefault();
            console.log('--- FORM SUBMIT BUTTON CLICKED ---');
            
            // Find the active step to ensure we have the right step number
            const activeStep = form.find('.form-step.active');
            if (activeStep.length) {
                const stepNum = parseInt(activeStep.attr('data-step'));
                if (stepNum) {
                    currentStep = stepNum;
                    console.log('Active step detected:', currentStep);
                }
            }
            
            console.log('Submitting from step:', currentStep);

            // Validate final step before submission
            if (!validateStep(currentStep)) {
                console.error('Validation failed. Submission halted.');
                return false;
            }
            console.log('Validation passed. Proceeding with submission.');

            showProcessingScreen();
            
            const formData = new FormData(form[0]);
            formData.append('action', formConfig.actionName);
            formData.append('nonce', form.find('input[name="nonce"]').val());

            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('AJAX Success:', response);
                    setTimeout(function() {
                        hideProcessingScreen();
                        if (response && response.success) {
                            if (response.data && response.data.redirect_url) {
                                sessionStorage.removeItem(storageKey); // Clear saved step
                                window.location.href = response.data.redirect_url;
                            } else {
                                // Show success message if no redirect
                                form.closest('.docket-fast-form, .docket-standard-form, .docket-vip-form').find('form, .docket-form-progress').hide();
                                form.closest('.docket-fast-form, .docket-standard-form, .docket-vip-form').find('.form-success').show();
                            }
                        } else {
                            alert('Submission Error: ' + (response && response.data && response.data.message ? response.data.message : 'An unknown error occurred.'));
                        }
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { status: status, error: error, xhr: xhr });
                    hideProcessingScreen();
                    alert('Connection Error. Please check your internet and try again.');
                }
            });
        });
        
        // Also handle form submit as fallback
        form.on('submit', function(e) {
            e.preventDefault();
            form.find('.btn-submit').trigger('click');
        });

        // --- CORE UI FUNCTIONS ---
        function showStep(step) {
            // Ensure step is within valid range
            if (step < 1) step = 1;
            if (step > steps.length) step = steps.length;
            
            // Update currentStep to match
            currentStep = step;
            
            steps.removeClass('active');
            steps.filter(`[data-step="${step}"]`).addClass('active');
        
            const progress = (step / steps.length) * 100;
            progressFill.css('width', progress + '%');
        
            progressDots.removeClass('active completed');
            progressDots.each(function(index) {
                const dotStep = index + 1;
                if (dotStep < step) {
                    $(this).addClass('completed');
                } else if (dotStep === step) {
                    $(this).addClass('active');
                }
            });
        
            form.find('.btn-prev').toggle(step > 1);

            const formOffset = form.offset();
            if (formOffset) {
                $('html, body').animate({ scrollTop: formOffset.top - 100 }, 300);
            }
        }

        function showValidationSummary(errors) {
            form.find('.validation-summary').remove();
            if (errors.length > 0) {
                const summaryHtml = `
                    <div class="validation-summary">
                        <strong>Please complete the following required fields:</strong>
                        <ul>${errors.map(error => `<li>${error}</li>`).join('')}</ul>
                    </div>`;
                steps.filter(`[data-step="${currentStep}"]`).prepend(summaryHtml);
            }
        }

        // --- VALIDATION LOGIC ---
        function validateStep(step) {
            const currentStepEl = steps.filter(`[data-step="${step}"]`);
            const required = currentStepEl.find('[required]:visible');
            let valid = true;
            let errors = [];
            
            currentStepEl.find('.error, .field-error, .validation-summary').removeClass('error').remove();
        
            required.each(function() {
                const $field = $(this);
                const $formField = $field.closest('.form-field');
                const fieldLabel = $formField.find('label').first().text().replace('*', '').trim();

                if ($field.is(':radio') && !$(`input[name="${$field.attr('name')}"]:checked`).length) {
                    valid = false;
                    if (errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    $formField.addClass('error');
                } else if ($field.is(':checkbox') && !$field.is(':checked')) {
                    valid = false;
                    if (errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    $formField.addClass('error');
                } else if (!$field.is(':radio, :checkbox') && (!$field.val() || $field.val().trim() === '')) {
                    valid = false;
                    if (errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    $formField.addClass('error').append('<div class="field-error">This field is required</div>');
                }
            });

            if (!valid) {
                showValidationSummary(errors);
            }
            return valid;
        }

        // --- DYNAMIC FIELD LOGIC ---
        form.on('change input', '.form-field.error', function() {
            $(this).removeClass('error').find('.field-error').remove();
        });

        form.on('change', 'input[name="logo_question"]', function() {
            const showUpload = $(this).val() === 'Yes';
            if (showUpload) {
                $('#logoUpload').slideDown();
                $('#logoUpload input').prop('required', true);
            } else {
                $('#logoUpload').slideUp();
                $('#logoUpload input').prop('required', false);
            }
        });

        // Handle "Match Primary Logo Color" question - show/hide color picker
        form.on('change', 'input[name="match_logo_color"]', function() {
            const showColorField = $(this).val() === 'No';
            if (showColorField) {
                $('#companyColorField').slideDown();
                $('#companyColorField input[name="company_colors"]').prop('required', true);
            } else {
                $('#companyColorField').slideUp();
                $('#companyColorField input[name="company_colors"]').prop('required', false);
            }
        });

        // Handle font selection question - show/hide font name field
        form.on('change', 'input[name="provide_font"]', function() {
            const showFontField = $(this).val() === 'Yes';
            if (showFontField) {
                $('#fontField').slideDown();
                $('#fontField input[name="font_name"]').prop('required', true);
            } else {
                $('#fontField').slideUp();
                $('#fontField input[name="font_name"]').prop('required', false);
            }
        });

        // Sync color picker with hex input (bidirectional)
        form.on('input change', '.hex-input', function() {
            const hexValue = $(this).val();
            if (hexValue.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
                $(this).closest('.color-input-wrapper').find('.color-picker').val(hexValue);
            }
        });

        form.on('input change', '.color-picker', function() {
            const colorValue = $(this).val();
            $(this).closest('.color-input-wrapper').find('.hex-input').val(colorValue);
        });

        // --- UI HELPERS ---
        function showProcessingScreen() {
            $('body').append('<div class="processing-overlay"><div class="processing-content"><h2>Processing...</h2></div></div>');
        }

        function hideProcessingScreen() {
            $('.processing-overlay').fadeOut(300, function() { $(this).remove(); });
        }
        
        // Initial setup
        showStep(currentStep);
    }

    // --- INITIALIZATION TRIGGER ---
    // Check if config is available from PHP
    if (typeof window.docketFormConfig !== 'undefined') {
        // Initialize with config from PHP
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initDocketForm(window.docketFormConfig);
            });
        } else {
            initDocketForm(window.docketFormConfig);
        }
    } else {
        // Fallback: Try to detect form type from DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-detect form type
                if ($('#fastBuildForm').length) {
                    initDocketForm({
                        formId: '#fastBuildForm',
                        formType: 'fast-build',
                        actionName: 'docket_submit_fast_build_form',
                        stepCount: 8
                    });
                } else if ($('#standardBuildForm').length) {
                    initDocketForm({
                        formId: '#standardBuildForm',
                        formType: 'standard-build',
                        actionName: 'docket_submit_standard_build_form',
                        stepCount: 8
                    });
                } else if ($('#websiteVipForm').length) {
                    initDocketForm({
                        formId: '#websiteVipForm',
                        formType: 'website-vip',
                        actionName: 'docket_submit_website_vip_form',
                        stepCount: 8
                    });
                }
            });
        } else {
            // Same auto-detection for already loaded DOM
            if ($('#fastBuildForm').length) {
                initDocketForm({
                    formId: '#fastBuildForm',
                    formType: 'fast-build',
                    actionName: 'docket_submit_fast_build_form',
                    stepCount: 8
                });
            } else if ($('#standardBuildForm').length) {
                initDocketForm({
                    formId: '#standardBuildForm',
                    formType: 'standard-build',
                    actionName: 'docket_submit_standard_build_form',
                    stepCount: 8
                });
            } else if ($('#websiteVipForm').length) {
                initDocketForm({
                    formId: '#websiteVipForm',
                    formType: 'website-vip',
                    actionName: 'docket_submit_website_vip_form',
                    stepCount: 8
                });
            }
        }
    }
})(jQuery);

