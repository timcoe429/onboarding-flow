(function($) {
    // This function initializes the entire multi-step form logic. It's designed
    // to run after the DOM is ready, making it compatible with AJAX-loaded content.
    function initFastBuildForm() {
    const form = $('#fastBuildForm');
        const steps = form.find('.form-step');
        const progressFill = form.find('.docket-progress-fill');
        const progressDots = form.find('.docket-progress-dots span');

        // Safeguard: If form elements don't exist, wait and retry.
        if (form.length === 0 || steps.length === 0) {
            setTimeout(initFastBuildForm, 100);
            return;
        }

    let currentStep = 1;
    
        // --- NAVIGATION & SUBMISSION ---
        // Use delegated event handlers attached to the form itself. This is robust
        // and ensures that buttons on all steps work correctly.
        form.on('click', '.btn-next', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });

        form.on('click', '.btn-prev', function() {
            currentStep--;
            showStep(currentStep);
        });

        form.on('submit', function(e) {
            e.preventDefault();
            console.log('--- FAST BUILD SUBMIT ---');
            if (!validateStep(currentStep)) {
                console.error('Validation Failed. Halting submission.');
                return;
            }

            showProcessingScreen();
            
            const formData = new FormData(this);
            formData.append('action', 'docket_submit_fast_build_form');
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
                        if (response.success && response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            alert('Submission Error: ' + (response.data.message || 'An unknown error occurred.'));
                        }
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { status, error });
                    hideProcessingScreen();
                    alert('Connection Error. Please check your internet and try again.');
                }
            });
        });

        // --- CORE UI FUNCTIONS ---
    function showStep(step) {
            steps.removeClass('active').hide();
            steps.filter(`[data-step="${step}"]`).addClass('active').show();
        
            const progress = (step / steps.length) * 100;
        progressFill.css('width', progress + '%');
        
        progressDots.removeClass('active completed');
        progressDots.each(function(index) {
            if (index + 1 < step) {
                $(this).addClass('completed');
            } else if (index + 1 === step) {
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
            $('#logoUpload').slideToggle(showUpload);
            $('#logoUpload input').prop('required', showUpload);
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
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFastBuildForm);
    } else {
        initFastBuildForm(); // For AJAX-loaded forms
    }
})(jQuery);
