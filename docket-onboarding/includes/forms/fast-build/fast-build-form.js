jQuery(document).ready(function($) {
    const form = $('#fastBuildForm');
    const steps = $('.form-step');
    const progressFill = $('.docket-progress-fill');
    const progressDots = $('.docket-progress-dots span');
    let currentStep = 1;
    
    // Navigation
    $('.btn-next').on('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    
    $('.btn-prev').on('click', function() {
        currentStep--;
        showStep(currentStep);
    });
    
    // Show step
    function showStep(step) {
        steps.removeClass('active');
        $(`.form-step[data-step="${step}"]`).addClass('active');
        
        // Update progress
        const progress = (step / 8) * 100;
        progressFill.css('width', progress + '%');
        
        // Update dots
        progressDots.removeClass('active completed');
        progressDots.each(function(index) {
            if (index + 1 < step) {
                $(this).addClass('completed');
            } else if (index + 1 === step) {
                $(this).addClass('active');
            }
        });
        
        // Scroll to top
        $('html, body').animate({ scrollTop: $('.docket-fast-form').offset().top - 50 }, 300);
    }
    
    // Validate step
    function validateStep(step) {
        const currentStepEl = $(`.form-step[data-step="${step}"]`);
        const required = currentStepEl.find('[required]:visible');
        let valid = true;
        let checkedRadios = {};
        let checkedCheckboxGroups = {};
        
        // Clear previous errors
        currentStepEl.find('.error').removeClass('error');
        
        required.each(function() {
            if ($(this).is(':radio')) {
                const name = $(this).attr('name');
                checkedRadios[name] = checkedRadios[name] || $(`input[name="${name}"]:checked`).length > 0;
                if (!checkedRadios[name]) {
                    valid = false;
                    $(this).closest('.radio-group, .radio-inline, .form-field').addClass('error');
                }
            } else if ($(this).is(':checkbox')) {
                const name = $(this).attr('name');
                const group = $(this).closest('.checkbox-group');
                if (group.length && !checkedCheckboxGroups[name]) {
                    checkedCheckboxGroups[name] = true;
                    if (!group.find('input:checked').length) {
                        valid = false;
                        group.addClass('error');
                    }
                } else if (!$(this).is(':checked')) {
                    valid = false;
                    $(this).closest('.checkbox-card, .form-field').addClass('error');
                }
            } else {
                const val = $(this).val();
                if (!val || val.trim().length === 0) {
                    valid = false;
                    $(this).addClass('error');
                }
            }
        });
        
        if (!valid) {
            alert('Please fill in all required fields');
            setTimeout(() => {
                currentStepEl.find('.error:first').find('input:first').focus();
            }, 100);
        }
        
        return valid;
    }
    
    // Remove error on change
    $(document).on('change input', '.error', function() {
        $(this).removeClass('error');
        $(this).closest('.error').removeClass('error');
    });
    
    // WordPress experience notice
    $('input[name="wordpress_exp"]').on('change', function() {
        const val = $(this).val();
        const notice = $('#wpNotice');
        
        if (val === 'Beginner') {
            notice.find('.notice-text').html('<strong>Important:</strong> Fast Build requires self-customization. Consider our Standard Build for a fully managed solution.');
            notice.show();
        } else if (val === 'Intermediate') {
            notice.find('.notice-text').html('<strong>Tip:</strong> Brush up on Elementor basics before launch to make the most of your website.');
            notice.show();
        } else {
            notice.hide();
        }
    });
    
    // Logo upload toggle
    $('input[name="logo_question"]').on('change', function() {
        if ($(this).val() === 'Yes') {
            $('#logoUpload').slideDown();
            $('#logoUpload input').attr('required', true);
        } else {
            $('#logoUpload').slideUp();
            $('#logoUpload input').attr('required', false);
        }
    });
    
    // Color picker integration
    $('.color-picker').on('input', function() {
        const hexValue = $(this).val();
        $(this).siblings('.hex-input').val(hexValue);
    });
    
    $('.hex-input').on('input', function() {
        const hexValue = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(hexValue)) {
            $(this).siblings('.color-picker').val(hexValue);
        }
    });

    // File upload display
    $('#logoFileInput').on('change', function() {
        const files = this.files;
        const fileList = $('#logoFileList');
        fileList.empty();
        
        if (files.length > 0) {
            fileList.append('<p style="margin-top: 10px; font-weight: 600;">Selected files:</p>');
            for (let i = 0; i < files.length; i++) {
                fileList.append(`<p style="margin: 5px 0; color: #6b7280; font-size: 14px;">• ${files[i].name}</p>`);
            }
        }
    });

    // Dumpster type toggles
    $('input[name="dumpster_types"]').on('change', function() {
        const value = $(this).val();
        const isChecked = $(this).is(':checked');
        
        if (value === 'Roll-Off') {
            if (isChecked) {
                $('#rollOffDetails').slideDown();
            } else {
                $('#rollOffDetails').slideUp();
            }
        } else if (value === 'Hook-Lift') {
            if (isChecked) {
                $('#hookLiftDetails').slideDown();
            } else {
                $('#hookLiftDetails').slideUp();
            }
        } else if (value === 'Dump Trailer') {
            if (isChecked) {
                $('#dumpTrailerDetails').slideDown();
            } else {
                $('#dumpTrailerDetails').slideUp();
            }
        }
    });

    // Show processing screen
    function showProcessingScreen() {
        const processingHTML = `
            <div class="processing-overlay">
                <div class="processing-content">
                    <div class="processing-spinner"></div>
                    <h2 class="processing-title">Processing Your Fast Build Order</h2>
                    <p class="processing-message">We're setting up your website build process. This may take a moment...</p>
                    
                    <div class="processing-progress">
                        <div class="processing-progress-bar"></div>
                    </div>
                    
                    <div class="processing-steps">
                        <div class="processing-step" data-step="1">
                            <div class="processing-step-icon">1</div>
                            <span>Validating form data</span>
                        </div>
                        <div class="processing-step" data-step="2">
                            <div class="processing-step-icon">2</div>
                            <span>Creating client profile</span>
                        </div>
                        <div class="processing-step" data-step="3">
                            <div class="processing-step-icon">3</div>
                            <span>Processing file uploads</span>
                        </div>
                        <div class="processing-step" data-step="4">
                            <div class="processing-step-icon">4</div>
                            <span>Generating project workspace</span>
                        </div>
                        <div class="processing-step" data-step="5">
                            <div class="processing-step-icon">5</div>
                            <span>Sending confirmation emails</span>
                        </div>
                    </div>
                    
                    <div class="processing-reassurance">
                        <strong>Don't worry!</strong> This process is secure and typically takes 30-60 seconds. Please don't close this window.
                    </div>
                    
                    <p class="processing-note">Building your dream website...</p>
                </div>
            </div>
        `;
        
        $('body').append(processingHTML);
        
        // Animate progress steps
        let currentProgressStep = 1;
        const totalSteps = 5;
        
        function animateStep() {
            $('.processing-step').removeClass('active completed');
            
            // Mark previous steps as completed
            for (let i = 1; i < currentProgressStep; i++) {
                $(`.processing-step[data-step="${i}"]`).addClass('completed');
            }
            
            // Mark current step as active
            $(`.processing-step[data-step="${currentProgressStep}"]`).addClass('active');
            
            // Update progress bar
            const progressPercent = (currentProgressStep / totalSteps) * 100;
            $('.processing-progress-bar').css('width', progressPercent + '%');
            
            currentProgressStep++;
            
            if (currentProgressStep <= totalSteps) {
                setTimeout(animateStep, 1200 + Math.random() * 800); // Randomize timing slightly
            }
        }
        
        // Start animation after a brief delay
        setTimeout(animateStep, 500);
    }
    
    // Hide processing screen
    function hideProcessingScreen() {
        $('.processing-overlay').fadeOut(300, function() {
            $(this).remove();
        });
    }

    // Form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        if (!validateStep(currentStep)) {
            return;
        }
        
        // Show enhanced processing screen
        showProcessingScreen();
        
        // Create FormData and collect all form data including files
        const formData = new FormData(this);
        formData.append('action', 'docket_submit_fast_build_form');
        
        // Submit via AJAX
        $.ajax({
            url: window.ajaxurl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Small delay to show completion
                setTimeout(function() {
                    hideProcessingScreen();
                    
                    if (response.success) {
                        // Check if there's a redirect URL
                        if (response.data && response.data.redirect_url) {
                            // Show brief success message before redirect
                            const successOverlay = `
                                <div class="processing-overlay">
                                    <div class="processing-content">
                                        <div style="width: 60px; height: 60px; background: #7eb10f; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 24px; font-weight: bold;">✓</div>
                                        <h2 class="processing-title">Order Submitted Successfully!</h2>
                                        <p class="processing-message">Redirecting to your client portal...</p>
                                    </div>
                                </div>
                            `;
                            $('body').append(successOverlay);
                            
                            setTimeout(() => {
                                window.location.href = response.data.redirect_url;
                            }, 1500);
                        } else {
                            // Fallback to showing success message
                            $('.docket-fast-form form').hide();
                            $('.docket-form-progress').hide();
                            $('.form-success').show();
                        }
                    } else {
                        alert('Error: ' + (response.data.message || 'Something went wrong'));
                    }
                }, 2000); // Allow processing animation to complete
            },
            error: function(xhr, status, error) {
                setTimeout(function() {
                    hideProcessingScreen();
                    console.error('AJAX Error:', status, error);
                    alert('Connection error. Please try again. Error: ' + error);
                }, 1000);
            }
        });
    });
    
    // Show full terms
    window.showFullTerms = function() {
        // You can implement a modal or expand the terms here
        alert('Full terms would be displayed here');
    };
});
