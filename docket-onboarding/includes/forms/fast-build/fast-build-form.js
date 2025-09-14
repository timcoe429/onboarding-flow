(function($) {
    // Execute when script loads (AJAX compatible)
    const form = $('#fastBuildForm');
    const steps = $('.form-step');
    const progressFill = $('.docket-progress-fill');
    const progressDots = $('.docket-progress-dots span');
    let currentStep = 1;
    
    // DEVELOPMENT MODE - Remove before going live
    const DEVELOPMENT_MODE = true;
    
    // Click step numbers to jump directly (development only)
    if (DEVELOPMENT_MODE) {
        $('.docket-progress-dots span').on('click', function() {
            const targetStep = parseInt($(this).data('step'));
            if (targetStep && targetStep <= 8) {
                currentStep = targetStep;
                showStep(currentStep);
            }
        });
        $('.docket-progress-dots span').css('cursor', 'pointer');
    }

    // Navigation
    $('.btn-next').on('click', function() {
        if (DEVELOPMENT_MODE || validateStep(currentStep)) {
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
        
        // Show/hide back button based on step
        if (step === 1) {
            $('.btn-prev').hide();
        } else {
            $('.btn-prev').show();
        }
        
        // Scroll to top
        $('html, body').animate({ scrollTop: $('.docket-fast-form').offset().top - 50 }, 300);
    }
    
    // Email validation regex
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Phone validation - accepts various formats
    function isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        const cleanPhone = phone.replace(/[\s\-\(\)\.]/g, '');
        return cleanPhone.length >= 10 && phoneRegex.test(cleanPhone);
    }
    
    // Validate step
    function validateStep(step) {
        const currentStepEl = $(`.form-step[data-step="${step}"]`);
        const required = currentStepEl.find('[required]:visible');
        let valid = true;
        let checkedRadios = {};
        let checkedCheckboxGroups = {};
        let missingFields = [];
        
        // Clear previous errors and validation message
        currentStepEl.find('.error').removeClass('error');
        currentStepEl.find('.field-error').remove();
        currentStepEl.find('#validationMessage').hide();
        
        required.each(function() {
            const $field = $(this);
            const fieldType = $field.attr('type');
            const fieldName = $field.attr('name');
            const val = $field.val();
            const $formField = $field.closest('.form-field');
            
            // Skip company color validation if user chose to match logo color
            if (fieldName === 'company_colors' && $('input[name="match_logo_color"]:checked').val() === 'Yes') {
                return;
            }
            
            if ($field.is(':radio')) {
                const name = $field.attr('name');
                checkedRadios[name] = checkedRadios[name] || $(`input[name="${name}"]:checked`).length > 0;
                if (!checkedRadios[name]) {
                    valid = false;
                    $field.closest('.radio-group, .radio-inline, .form-field').addClass('error');
                    const label = $formField.find('label').text().replace('*', '').trim();
                    missingFields.push(label);
                    $formField.append('<div class="field-error">Please select an option for ' + label + '</div>');
                }
            } else if ($field.is(':checkbox')) {
                const name = $field.attr('name');
                const group = $field.closest('.checkbox-group');
                if (group.length && !checkedCheckboxGroups[name]) {
                    checkedCheckboxGroups[name] = true;
                    if (!group.find('input:checked').length) {
                        valid = false;
                        group.addClass('error');
                        missingFields.push('Please select at least one option');
                        group.append('<div class="field-error">Please select at least one option</div>');
                    }
                } else if (!$field.is(':checked')) {
                    valid = false;
                    $field.closest('.checkbox-card, .form-field').addClass('error');
                    const label = $formField.find('label').text().replace('*', '').trim();
                    missingFields.push(label);
                    $formField.append('<div class="field-error">Please check this required field</div>');
                }
            } else {
                // Text, email, tel fields
                let errorMessage = '';
                
                if (!val || val.trim().length === 0) {
                    valid = false;
                    $field.addClass('error');
                    const label = $formField.find('label').text().replace('*', '').trim();
                    errorMessage = label + ' is required';
                    missingFields.push(label);
                } else {
                    // Format validation for specific field types
                    if (fieldType === 'email' || fieldName.includes('email')) {
                        if (!isValidEmail(val)) {
                            valid = false;
                            $field.addClass('error');
                            errorMessage = 'Please enter a valid email address';
                        }
                    } else if (fieldType === 'tel' || fieldName.includes('phone')) {
                        if (!isValidPhone(val)) {
                            valid = false;
                            $field.addClass('error');
                            errorMessage = 'Please enter a valid phone number (at least 10 digits)';
                        }
                    }
                }
                
                if (errorMessage) {
                    $formField.append('<div class="field-error">' + errorMessage + '</div>');
                }
            }
        });
        
        // Custom validation for company colors field
        if (step === 6) {
            const matchLogoColor = $('input[name="match_logo_color"]:checked').val();
            const companyColorsField = $('input[name="company_colors"]');
            
            if (matchLogoColor === 'No') {
                const colorValue = companyColorsField.val().trim();
                if (!colorValue || !colorValue.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
                    valid = false;
                    companyColorsField.addClass('error');
                    companyColorsField.closest('.form-field').addClass('error');
                    companyColorsField.closest('.form-field').append('<div class="field-error">Please enter a valid HEX color code (e.g., #00008B)</div>');
                }
            }
        }
        
        // Show validation message if there are missing fields
        if (!valid && missingFields.length > 0) {
            const validationMessage = currentStepEl.find('#validationMessage');
            if (validationMessage.length > 0) {
                const validationList = validationMessage.find('#validationList');
                validationList.empty();
                missingFields.forEach(function(field) {
                    validationList.append('<li>' + field + '</li>');
                });
                validationMessage.show();
            }
        }
        
        return valid;
    }
    
    // Remove error on change
    $(document).on('change input', '.error', function() {
        $(this).removeClass('error');
        $(this).closest('.error').removeClass('error');
        $(this).closest('.form-field').find('.field-error').remove();
    });
    
    // Match logo color radio button change
    $(document).on('change', 'input[name="match_logo_color"]', function() {
        const matchLogo = $(this).val();
        const companyColorField = $('#companyColorField');
        
        if (matchLogo === 'Yes') {
            companyColorField.hide();
            // Clear any validation errors for company color fields
            companyColorField.find('input').removeClass('error');
            companyColorField.find('.field-error').remove();
        } else {
            companyColorField.show();
        }
    });
    
    
    // Dumpster type checkbox change - show sizes field if any are selected
    $(document).on('change', 'input[name="dumpster_types[]"]', function() {
        const checkedTypes = $('input[name="dumpster_types[]"]:checked').length;
        const sizesField = $('#dumpsterSizesField');
        
        if (checkedTypes > 0) {
            sizesField.show();
        } else {
            sizesField.hide();
            // Clear the textarea when hidden
            sizesField.find('textarea').val('');
        }
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

    // Universal Dumpster Entry System
    let dumpsterCounter = 0;

    // Add dumpster entry
    $(document).on('click', '.add-dumpster-btn', function() {
        const type = $(this).data('type');
        const container = $(this).siblings('.dumpster-entries');
        dumpsterCounter++;
        
        const entryHtml = createDumpsterEntry(type, dumpsterCounter);
        container.append(entryHtml);
    });

    // Delete dumpster entry
    $(document).on('click', '.delete-dumpster-btn', function() {
        $(this).closest('.dumpster-entry').remove();
    });

    // Create dumpster entry HTML
    function createDumpsterEntry(type, id) {
        const sizes = [7, 10, 12, 15, 20, 30, 40];
        const sizeOptions = [];
        sizes.forEach(size => {
            sizeOptions.push(`<option value="${size}">${size} yd</option>`);
        });

        const tonOptions = [];
        for (let i = 1; i <= 10; i++) {
            tonOptions.push(`<option value="${i}">${i} ton${i > 1 ? 's' : ''}</option>`);
        }

        return `
            <div class="dumpster-entry">
                <button type="button" class="delete-dumpster-btn">×</button>
                <div class="dumpster-entry-grid">
                    <div class="dumpster-field">
                        <label>Size</label>
                        <select name="${type}_size_${id}" required>
                            <option value="">Select Size</option>
                            ${sizeOptions.join('')}
                        </select>
                    </div>
                    <div class="dumpster-field">
                        <label>Tons Allowed</label>
                        <select name="${type}_tons_${id}" required>
                            <option value="">Select Tons</option>
                            ${tonOptions.join('')}
                        </select>
                    </div>
                    <div class="dumpster-field">
                        <label>Rental Period</label>
                        <input type="text" name="${type}_period_${id}" placeholder="e.g., 1, 3, and 7 Day Rentals" required>
                    </div>
                    <div class="dumpster-field">
                        <label>Starting Price</label>
                        <input type="text" name="${type}_price_${id}" placeholder="e.g., $299" required>
                    </div>
                </div>
            </div>
        `;
    }

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
    $(document).on('change', '.dumpster-type', function() {
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
})(jQuery);
