(function($) {
    // This function initializes the entire multi-step form logic. It's designed
    // to run after the DOM is ready, making it compatible with AJAX-loaded content.
    function initStandardBuildForm() {
    const form = $('#standardBuildForm');
    const steps = $('.form-step');
    const progressFill = $('.docket-progress-fill');
    const progressDots = $('.docket-progress-dots span');

        // Safeguard: If the form elements don't exist yet, wait and retry.
        if (form.length === 0 || steps.length === 0) {
            setTimeout(initStandardBuildForm, 100);
            return;
        }

    let currentStep = 1;
    
        // Allows developers to skip validation by holding Shift while clicking 'Next'.
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

        // --- NAVIGATION ---
        $(document).on('click', '#standardBuildForm .btn-next', function(event) {
        const skipValidation = DEVELOPMENT_MODE && event.shiftKey;
        if (skipValidation || validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    
        $(document).on('click', '#standardBuildForm .btn-prev', function() {
        currentStep--;
        showStep(currentStep);
    });
    
        // --- FORM SUBMISSION ---
        $(document).on('click', '#standardBuildForm .btn-submit', function(e) {
            e.preventDefault();
            console.log('--- SUBMIT BUTTON CLICKED ---');

            if (!validateStep(currentStep)) {
                console.error('Validation failed. Submission halted.');
                return;
            }
            console.log('Validation passed. Proceeding with submission.');

            showProcessingScreen();
            
            const formData = new FormData(form[0]);
            formData.append('action', 'docket_submit_standard_build_form');
            formData.append('nonce', form.find('input[name="nonce"]').val());
            
            console.log('Making AJAX call to:', docket_ajax.ajax_url);
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
                        if (response.success) {
                            if (response.data && response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                $('.docket-standard-form form, .docket-form-progress').hide();
                                $('.form-success').show();
                            }
                        } else {
                            console.error('AJAX call returned success=false.', response.data);
                            alert('Submission Error: ' + (response.data.message || 'An unknown error occurred.'));
                        }
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { status: status, error: error, xhr: xhr });
                    setTimeout(function() {
                        hideProcessingScreen();
                        alert('Connection Error: Could not submit the form. Please check your internet connection and try again.');
                    }, 1000);
                }
            });
        });

        // --- CORE UI FUNCTIONS ---
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
        
            $('.btn-prev').toggle(step > 1);

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
                        <div class="validation-summary-header"><strong>Please complete the following required fields:</strong></div>
                        <ul>${errors.map(error => `<li>${error}</li>`).join('')}</ul>
                    </div>`;
                $(`.form-step[data-step="${currentStep}"]`).prepend(summaryHtml);
                const summaryOffset = $('.validation-summary').offset();
                if (summaryOffset) {
                    $('html, body').animate({ scrollTop: summaryOffset.top - 100 }, 300);
                }
            }
        }

        // --- VALIDATION LOGIC ---
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function isValidPhone(phone) {
            const digitsOnly = phone.replace(/[^\d]/g, '');
            return digitsOnly.length >= 10;
        }

    function validateStep(step) {
        const currentStepEl = $(`.form-step[data-step="${step}"]`);
        const required = currentStepEl.find('[required]:visible');
        let valid = true;
        let errors = [];
            let processedRadios = {};
        
        currentStepEl.find('.error').removeClass('error');
        currentStepEl.find('.field-error').remove();
        $('.validation-summary').remove();
        
        required.each(function() {
            const $field = $(this);
            const fieldType = $field.attr('type');
            const fieldName = $field.attr('name');
            const val = $field.val();
            const $formField = $field.closest('.form-field');
                const fieldLabel = $formField.find('label').first().text().replace('*', '').trim();
                let errorMessage = '';
            
            if ($field.is(':radio')) {
                    if (!processedRadios[fieldName]) {
                        if ($(`input[name="${fieldName}"]:checked`).length === 0) {
                        valid = false;
                            $field.closest('.radio-group, .form-field').addClass('error');
                            if (fieldLabel && errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                        }
                        processedRadios[fieldName] = true;
                }
            } else if ($field.is(':checkbox')) {
                    if (!$field.is(':checked')) {
                        valid = false;
                        $field.closest('.checkbox-card, .form-field').addClass('error');
                        if (fieldLabel && errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    }
                } else { // Text, Email, Tel, etc.
                if (!val || val.trim().length === 0) {
                    valid = false;
                    errorMessage = 'This field is required';
                    } else if ((fieldType === 'email' || fieldName.includes('email')) && !isValidEmail(val)) {
                            valid = false;
                        errorMessage = 'Invalid email format';
                    } else if ((fieldType === 'tel' || fieldName.includes('phone')) && !isValidPhone(val)) {
                            valid = false;
                        errorMessage = 'Invalid phone format (at least 10 digits)';
                }
                
                if (errorMessage) {
                        $field.addClass('error');
                        $formField.append(`<div class="field-error">${errorMessage}</div>`);
                        if (fieldLabel && errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    }
                }
            });

            if (!valid) {
                showValidationSummary(errors);
            }
            return valid;
        }

        // --- DYNAMIC FIELD LOGIC ---
        $(document).on('change input', '.form-field.error, .error', function() {
            $(this).removeClass('error').closest('.error').removeClass('error');
        $(this).closest('.form-field').find('.field-error').remove();
            if ($(`.form-step[data-step="${currentStep}"]`).find('.error').length === 0) {
                $('.validation-summary').remove();
            }
        });

        function setupConditionalField(radioName, targetId, requiredSelector) {
             $(document).on('change', `input[name="${radioName}"]`, function() {
                const show = $(this).val() === 'Yes';
                $(targetId).slideToggle(show);
                if (requiredSelector) {
                    $(targetId).find(requiredSelector).prop('required', show);
                }
             });
        }
        
        $(document).on('change', 'input[name="match_logo_color"]', function() {
             $('#companyColorField').slideToggle($(this).val() === 'No');
        });

        setupConditionalField('logo_question', '#logoUpload', 'input');
        setupConditionalField('provide_content_now', '#contentFields', 'input[type="radio"]');
        setupConditionalField('provide_tagline', '#taglineField', 'textarea');
        setupConditionalField('provide_faqs', '#faqField', 'textarea');
        setupConditionalField('provide_benefits', '#benefitsField', 'textarea');
        setupConditionalField('provide_footer', '#footerField', null);
        setupConditionalField('provide_font', '#fontField', 'input');
        
        // --- UI HELPERS ---
    function showProcessingScreen() {
            const processingHTML = `<div class="processing-overlay"><div class="processing-content"><div class="processing-spinner"></div><h2>Processing Your Order</h2><p>This may take a moment...</p></div></div>`;
        $('body').append(processingHTML);
        }

    function hideProcessingScreen() {
            $('.processing-overlay').fadeOut(300, function() { $(this).remove(); });
        }
    }

    // --- INITIALIZATION TRIGGER ---
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStandardBuildForm);
    } else {
        initStandardBuildForm();
    }
})(jQuery);
