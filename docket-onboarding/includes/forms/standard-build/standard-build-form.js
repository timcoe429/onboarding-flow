(function($) {
    // This function initializes the entire multi-step form logic.
    function initStandardBuildForm() {
    const form = $('#standardBuildForm');
    const steps = $('.form-step');
    const progressFill = $('.docket-progress-fill');
    const progressDots = $('.docket-progress-dots span');

        // If the main form elements aren't on the page yet, wait a moment and retry.
        // This is a safeguard for forms loaded via AJAX.
        if (form.length === 0 || steps.length === 0) {
            setTimeout(initStandardBuildForm, 100);
            return;
        }

    let currentStep = 1;
    
        // DEVELOPMENT MODE allows skipping validation by holding Shift while clicking 'Next'.
    const DEVELOPMENT_MODE = true;
    
    if (DEVELOPMENT_MODE) {
        $('.docket-progress-dots span').on('click', function() {
            const targetStep = parseInt($(this).data('step'));
            if (targetStep && targetStep <= 8) {
                currentStep = targetStep;
                showStep(currentStep);
            }
            }).css('cursor', 'pointer');
    }

        // --- Navigation ---
    $('.btn-next').on('click', function(event) {
        const skipValidation = DEVELOPMENT_MODE && event.shiftKey;
        if (skipValidation || validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    
    $('.btn-prev').on('click', function() {
        currentStep--;
        showStep(currentStep);
    });
    
        // --- Core Functions ---
    function showStep(step) {
        steps.removeClass('active');
        $(`.form-step[data-step="${step}"]`).addClass('active');
        
        const progress = (step / 8) * 100;
        progressFill.css('width', progress + '%');
        
        progressDots.removeClass('active completed');
        progressDots.each(function(index) {
            if (index + 1 < step) {
                $(this).addClass('completed');
            } else if (index + 1 === step) {
                $(this).addClass('active');
            }
        });
        
        // Show/hide back button based on step
        if (step === 1) {
            $('.btn-prev').hide();
        } else {
            $('.btn-prev').show();
        }
        
            // Scroll to the top of the form for better user experience on step change.
            const formOffset = $('.docket-standard-form').offset();
            if (formOffset) {
                $('html, body').animate({ scrollTop: formOffset.top - 50 }, 300);
            }
        }

    function showValidationSummary(errors) {
        $('.validation-summary').remove();
        if (errors.length > 0) {
            const summaryHtml = `
                <div class="validation-summary">
                    <div class="validation-summary-header">
                        <span class="validation-icon">⚠️</span>
                        <strong>Please complete the following required fields:</strong>
                    </div>
                    <ul class="validation-errors">
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                </div>
            `;
            const currentStepEl = $(`.form-step[data-step="${currentStep}"]`);
            currentStepEl.prepend(summaryHtml);
            
                const summaryOffset = $('.validation-summary').offset();
                if (summaryOffset) {
                    $('html, body').animate({ scrollTop: summaryOffset.top - 100 }, 300);
                }
            }
        }

        // --- Validation ---
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            const cleanPhone = phone.replace(/[\s\-\(\)\.]/g, '');
            const digitsOnly = cleanPhone.replace(/\+/g, '');
            return digitsOnly.length >= 10 && /^\+?[\d]{10,}$/.test(cleanPhone);
        }

    function validateStep(step) {
        const currentStepEl = $(`.form-step[data-step="${step}"]`);
        const required = currentStepEl.find('[required]:visible');
        let valid = true;
        let checkedRadios = {};
        let checkedCheckboxGroups = {};
        let errors = [];
        
        currentStepEl.find('.error').removeClass('error');
        currentStepEl.find('.field-error').remove();
        $('.validation-summary').remove();
        
        required.each(function() {
            const $field = $(this);
            const fieldType = $field.attr('type');
            const fieldName = $field.attr('name');
            const val = $field.val();
            const $formField = $field.closest('.form-field');
            
            if (fieldName === 'company_colors' && $('input[name="match_logo_color"]:checked').val() === 'Yes') {
                return;
            }
            
            if ($field.is(':radio')) {
                const name = $field.attr('name');
                if (!checkedRadios[name]) {
                    checkedRadios[name] = $(`input[name="${name}"]:checked`).length > 0;
                    if (!checkedRadios[name]) {
                        valid = false;
                            const $parent = $field.closest('.radio-group, .radio-inline, .form-field');
                            $parent.addClass('error');
                            const label = $parent.find('label').first().text().replace('*', '').trim();
                            if (label && errors.indexOf(label) === -1) errors.push(label);
                        }
                }
            } else if ($field.is(':checkbox')) {
                     if (!$field.is(':checked')) {
                        valid = false;
                        const $parent = $field.closest('.checkbox-card, .form-field');
                        $parent.addClass('error');
                        const label = $parent.find('label').first().text().replace('*', '').trim();
                        if (label && errors.indexOf(label) === -1) errors.push(label);
                }
            } else {
                let errorMessage = '';
                let fieldLabel = $formField.find('label').first().text().replace('*', '').trim();
                
                if (!val || val.trim().length === 0) {
                    valid = false;
                    errorMessage = 'This field is required';
                        if (fieldLabel && errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                } else {
                    if (fieldType === 'email' || fieldName.includes('email')) {
                        if (!isValidEmail(val)) {
                            valid = false;
                            errorMessage = 'Please enter a valid email address';
                                if (fieldLabel && errors.indexOf(fieldLabel) === -1) errors.push(`${fieldLabel} - Invalid format`);
                        }
                    } else if (fieldType === 'tel' || fieldName.includes('phone')) {
                        if (!isValidPhone(val)) {
                            valid = false;
                            errorMessage = 'Please enter a valid phone number (at least 10 digits)';
                                if (fieldLabel && errors.indexOf(fieldLabel) === -1) errors.push(`${fieldLabel} - Invalid format`);
                            }
                    }
                }
                
                if (errorMessage) {
                        $field.addClass('error');
                    $formField.append('<div class="field-error">' + errorMessage + '</div>');
                }
            }
        });
        
            if (!valid) {
                showValidationSummary(errors);
        }
        
        return valid;
    }
    
        // --- Field-Specific Logic ---
    $(document).on('change input', '.error', function() {
            $(this).removeClass('error').closest('.error').removeClass('error');
        $(this).closest('.form-field').find('.field-error').remove();
            if ($(`.form-step[data-step="${currentStep}"]`).find('.error').length === 0) {
                $('.validation-summary').remove();
            }
        });
        
        // ... (Other handlers like match_logo_color, template selection, etc.) ...

        // --- FORM SUBMISSION ---
        // Using a delegated event handler attached to the document for robustness.
        // This ensures the handler works even if the form is loaded dynamically.
        $(document).on('submit', '#standardBuildForm', function(e) {
            console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
        e.preventDefault();
        
        if (!validateStep(currentStep)) {
            return;
        }
        
        showProcessingScreen();
        
        const formData = new FormData(this);
        formData.append('action', 'docket_submit_standard_build_form');
        formData.append('nonce', $('#standardBuildForm input[name="nonce"]').val());
        
        $.ajax({
            url: docket_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                setTimeout(function() {
                    hideProcessingScreen();
                    if (response.success) {
                        if (response.data && response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                        } else {
                                $('.docket-standard-form form, .docket-form-progress').hide();
                            $('.form-success').show();
                        }
                    } else {
                        alert('Error: ' + (response.data.message || 'Something went wrong'));
                    }
                    }, 2500);
            },
            error: function(xhr, status, error) {
                setTimeout(function() {
                    hideProcessingScreen();
                    alert('Connection error. Please try again. Error: ' + error);
                }, 1000);
            }
        });
    });
    
        // --- UI Helpers ---
        function showProcessingScreen() {
            // ... (code for showing the processing overlay) ...
        }

        function hideProcessingScreen() {
            $('.processing-overlay').fadeOut(300, function() { $(this).remove(); });
        }

    } // --- End of initStandardBuildForm ---

    // --- Initialization Trigger ---
    // This ensures the init function runs after the document is fully loaded,
    // which is crucial for forms loaded via AJAX.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStandardBuildForm);
    } else {
        // The DOM was already loaded, safe to initialize immediately.
        initStandardBuildForm();
    }
})(jQuery);
