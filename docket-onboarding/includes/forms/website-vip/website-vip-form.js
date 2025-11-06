(function($) {
    function initWebsiteVipForm() {
    const form = $('#websiteVipForm');
        const steps = form.find('.form-step');
        const progressFill = form.find('.docket-progress-fill');
        const progressDots = form.find('.docket-progress-dots span');

        if (form.length === 0 || steps.length === 0) {
            setTimeout(initWebsiteVipForm, 100);
            return;
        }

    let currentStep = 1;
    
        // --- NAVIGATION & SUBMISSION ---
        form.on('click', '.btn-next', function(e) {
            e.preventDefault();
            currentStep++;
            showStep(currentStep);
        });

        form.on('click', '.btn-prev', function(e) {
            e.preventDefault();
            currentStep--;
            showStep(currentStep);
        });

    form.on('submit', function(e) {
        e.preventDefault();
            console.log('--- WEBSITE VIP SUBMIT ---');
        if (!validateStep(currentStep)) {
                console.error('Validation Failed. Halting submission.');
            return;
        }
        
        showProcessingScreen();
        
        const formData = new FormData(this);
        formData.append('action', 'docket_submit_website_vip_form');
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
    
        // --- CORE UI & VALIDATION ---
        function showStep(step) {
            steps.removeClass('active').hide();
            steps.filter(`[data-step="${step}"]`).addClass('active').show();
            // ... (progress bar and other UI updates) ...
        }

        function validateStep(step) {
            // ... (A simplified but complete validation logic similar to the other forms) ...
            const currentStepEl = steps.filter(`[data-step="${step}"]`);
            const required = currentStepEl.find('[required]:visible');
            let valid = true;
            
            required.each(function() {
                if (!this.checkValidity()) {
                    valid = false;
                    $(this).closest('.form-field').addClass('error');
                }
            });
            
            return valid;
        }

        // --- DYNAMIC & UI HELPERS ---
        // ... (Conditional field logic and processing screens) ...

        showStep(currentStep); // Initial call
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWebsiteVipForm);
    } else {
        initWebsiteVipForm();
    }
})(jQuery);
