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

        // Guard: Prevent duplicate initialization for the same form
        const initKey = 'docketFormInit_' + formConfig.formId.replace('#', '');
        if (window[initKey]) {
            return; // Already initialized for this form
        }
        window[initKey] = true;

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
        const formDataKey = formConfig.formType + 'FormData';
        let currentStep = parseInt(sessionStorage.getItem(storageKey)) || 1;

        // --- FORM DATA PERSISTENCE (Phase 1: Simple Fields Only) ---
        // Save form data to localStorage (excludes file inputs)
        function saveFormData() {
            try {
                // Use serializeArray to get all form data (excludes file inputs automatically)
                const formData = form.serializeArray();
                const dataToSave = {};
                
                // Convert array to object, handling arrays (checkboxes, multi-selects)
                formData.forEach(function(field) {
                    if (dataToSave[field.name]) {
                        // If field already exists, convert to array
                        if (!Array.isArray(dataToSave[field.name])) {
                            dataToSave[field.name] = [dataToSave[field.name]];
                        }
                        dataToSave[field.name].push(field.value);
                    } else {
                        dataToSave[field.name] = field.value;
                    }
                });
                
                // Also save textarea and select values that might not be in serializeArray
                form.find('textarea, select').each(function() {
                    const $field = $(this);
                    const name = $field.attr('name');
                    if (name && !dataToSave[name]) {
                        if ($field.is('select[multiple]')) {
                            const values = $field.val();
                            if (values && values.length > 0) {
                                dataToSave[name] = values;
                            }
                        } else {
                            const value = $field.val();
                            if (value) {
                                dataToSave[name] = value;
                            }
                        }
                    }
                });
                
                localStorage.setItem(formDataKey, JSON.stringify(dataToSave));
            } catch (e) {
                console.error('Error saving form data:', e);
            }
        }
        
        // Restore form data from localStorage
        function restoreFormData() {
            try {
                const savedData = localStorage.getItem(formDataKey);
                if (!savedData) return;
                
                const formData = JSON.parse(savedData);
                
                // Phase 1: Restore checkboxes and radios first to trigger conditional visibility
                Object.keys(formData).forEach(function(name) {
                    const value = formData[name];
                    const $fields = form.find('[name="' + name + '"]');
                    
                    if ($fields.length === 0) return;
                    
                    // Handle checkboxes and radios first
                    if ($fields.first().is(':checkbox') || $fields.first().is(':radio')) {
                        if (Array.isArray(value)) {
                            // Handle checkbox groups
                            $fields.each(function() {
                                const $field = $(this);
                                if (value.indexOf($field.val()) !== -1) {
                                    $field.prop('checked', true);
                                }
                            });
                        } else {
                            // Handle single checkbox or radio
                            $fields.each(function() {
                                const $field = $(this);
                                if ($field.val() === value) {
                                    $field.prop('checked', true);
                                }
                            });
                        }
                    }
                });
                
                // Trigger change events to show/hide conditional sections
                form.find('input[type="checkbox"], input[type="radio"]').filter(':checked').trigger('change');
                
                // Phase 2: Restore other fields after sections are visible
                Object.keys(formData).forEach(function(name) {
                    const value = formData[name];
                    const $fields = form.find('[name="' + name + '"]');
                    
                    if ($fields.length === 0) return;
                    
                    // Skip checkboxes and radios (already handled)
                    if ($fields.first().is(':checkbox') || $fields.first().is(':radio')) {
                        return;
                    }
                    
                    // Skip file inputs (can't restore)
                    if ($fields.first().is('input[type="file"]')) {
                        return;
                    }
                    
                    // Handle other field types
                    const $field = $fields.first();
                    if (Array.isArray(value)) {
                        // Multi-select
                        if ($field.is('select[multiple]')) {
                            $field.val(value);
                        }
                    } else {
                        // Single value fields
                        if ($field.is('select')) {
                            $field.val(value);
                        } else {
                            $field.val(value);
                        }
                    }
                });
            } catch (e) {
                console.error('Error restoring form data:', e);
            }
        }
        
        // Clear saved form data
        function clearFormData() {
            try {
                localStorage.removeItem(formDataKey);
                sessionStorage.removeItem(storageKey);
            } catch (e) {
                console.error('Error clearing form data:', e);
            }
        }
        
        // Auto-save form data on any field change (debounced)
        let saveTimeout;
        form.on('input change', 'input:not([type="file"]), select, textarea', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveFormData, 500); // Save 500ms after user stops typing
        });

        // --- NAVIGATION & SUBMISSION ---
        // Use delegated event handlers attached to the form itself
        form.on('click', '.btn-next', function(e) {
            e.preventDefault();
            
            // Save form data before advancing
            saveFormData();
            
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
            // Save form data before going back
            saveFormData();
            
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

            // Save form data before submission
            saveFormData();

            // Validate ALL steps before submission (not just current step)
            let allStepsValid = true;
            let firstInvalidStep = null;
            
            for (let step = 1; step <= formConfig.stepCount; step++) {
                if (!validateStep(step)) {
                    allStepsValid = false;
                    if (!firstInvalidStep) {
                        firstInvalidStep = step;
                    }
                }
            }
            
            if (!allStepsValid) {
                console.error('Validation failed on step:', firstInvalidStep);
                // Navigate to the first invalid step
                showStep(firstInvalidStep);
                // Scroll to top to show validation errors
                $('html, body').animate({ scrollTop: 0 }, 300);
                return false;
            }
            
            console.log('All steps validated. Proceeding with submission.');

            showProcessingScreen();
            
            const formData = new FormData(form[0]);
            formData.append('action', formConfig.actionName);
            formData.append('nonce', form.find('input[name="nonce"]').val());
            formData.append('current_page_url', window.location.href);

            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Success:', response);
                    setTimeout(function() {
                        if (response && response.success) {
                            // Clear saved form data on successful submission
                            clearFormData();
                            
                            if (response.data && response.data.redirect_url) {
                                // Keep processing screen visible and redirect directly
                                window.location.href = response.data.redirect_url;
                            } else {
                                // Show success message if no redirect
                                hideProcessingScreen();
                                form.closest('.docket-fast-form, .docket-standard-form, .docket-vip-form').find('form, .docket-form-progress').hide();
                                form.closest('.docket-fast-form, .docket-standard-form, .docket-vip-form').find('.form-success').show();
                            }
                        } else {
                            hideProcessingScreen();
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
        
            // Helper function to get field label (handles both .form-field and .dumpster-field containers)
            function getFieldLabel($field) {
                // Try .dumpster-field first (for dynamic dumpster fields)
                let $container = $field.closest('.dumpster-field');
                if ($container.length) {
                    const label = $container.find('label').first().text().replace('*', '').trim();
                    if (label) return label;
                }
                
                // Fall back to .form-field
                $container = $field.closest('.form-field');
                if ($container.length) {
                    const label = $container.find('label').first().text().replace('*', '').trim();
                    if (label) return label;
                }
                
                // Fallback: use placeholder or name attribute
                const placeholder = $field.attr('placeholder');
                if (placeholder) return placeholder;
                
                const name = $field.attr('name');
                if (name) {
                    // Convert field name to readable label
                    return name.replace(/dumpster_/g, '').replace(/_/g, ' ').replace(/\[]/g, '').replace(/\b\w/g, l => l.toUpperCase());
                }
                
                return 'Required field';
            }
            
            // Helper function to find error container (handles both .dumpster-field and .form-field)
            function getErrorContainer($field) {
                // Try .dumpster-field first
                let $container = $field.closest('.dumpster-field');
                if ($container.length) return $container;
                
                // Fall back to .form-field
                $container = $field.closest('.form-field');
                if ($container.length) return $container;
                
                // Final fallback: use field's parent
                return $field.parent();
            }
            
            // Group required checkboxes by name to handle checkbox groups correctly
            const checkboxGroups = {};
            const otherRequiredFields = [];
            
            required.each(function() {
                const $field = $(this);
                if ($field.is(':checkbox')) {
                    const name = $field.attr('name');
                    if (!checkboxGroups[name]) {
                        checkboxGroups[name] = [];
                    }
                    checkboxGroups[name].push($field);
                } else {
                    otherRequiredFields.push($field);
                }
            });
            
            // Validate checkbox groups (at least one must be checked)
            Object.keys(checkboxGroups).forEach(function(name) {
                const group = checkboxGroups[name];
                const atLeastOneChecked = group.some(function($checkbox) {
                    return $checkbox.is(':checked');
                });
                
                if (!atLeastOneChecked) {
                    valid = false;
                    const fieldLabel = getFieldLabel(group[0]);
                    if (errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    const $errorContainer = getErrorContainer(group[0]);
                    $errorContainer.addClass('error');
                }
            });
            
            // Validate other required fields (radio, text, single checkboxes, etc.)
            otherRequiredFields.forEach(function($field) {
                const fieldLabel = getFieldLabel($field);
                const $errorContainer = getErrorContainer($field);

                if ($field.is(':radio') && !$(`input[name="${$field.attr('name')}"]:checked`).length) {
                    valid = false;
                    if (errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    $errorContainer.addClass('error');
                } else if (!$field.is(':radio, :checkbox') && (!$field.val() || $field.val().trim() === '')) {
                    valid = false;
                    if (errors.indexOf(fieldLabel) === -1) errors.push(fieldLabel);
                    $errorContainer.addClass('error');
                    // Only add inline error if it doesn't already exist
                    if (!$errorContainer.find('.field-error').length) {
                        $errorContainer.append('<div class="field-error">This field is required</div>');
                    }
                }
            });

            if (!valid) {
                showValidationSummary(errors);
            }
            return valid;
        }

        // --- DYNAMIC FIELD LOGIC ---
        form.on('change input', '.form-field.error, .dumpster-field.error', function() {
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

        // Handle logo file upload - show file list
        form.on('change', '#logoFileInput', function() {
            const files = this.files;
            const $fileList = $('#logoFileList');
            $fileList.empty();
            
            if (files.length > 0) {
                Array.from(files).forEach(function(file) {
                    const fileItem = $('<div class="file-item"></div>');
                    const fileName = $('<span class="file-name"></span>').text(file.name);
                    const fileSize = $('<span class="file-size"></span>').text(formatFileSize(file.size));
                    const filePreview = $('<div class="file-preview"></div>');
                    
                    // Create preview for images
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = $('<img>').attr('src', e.target.result);
                            filePreview.append(img);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        filePreview.append('<span class="file-icon">📄</span>');
                    }
                    
                    fileItem.append(filePreview);
                    fileItem.append(fileName);
                    fileItem.append(fileSize);
                    $fileList.append(fileItem);
                });
                
                // Update upload text
                const $uploadText = $('#logoUpload .file-upload-text span');
                $uploadText.text(files.length === 1 ? '1 file selected' : files.length + ' files selected');
            } else {
                // Reset upload text
                const $uploadText = $('#logoUpload .file-upload-text span');
                $uploadText.text('Click to upload or drag files here');
            }
        });

        // Handle dumpster images file upload - show file list
        $(document).on('change', formConfig.formId + ' #dumpsterFileInput', function() {
            const files = this.files;
            const $fileList = $(formConfig.formId).find('#dumpsterFileList');
            $fileList.empty();
            
            if (files.length > 0) {
                Array.from(files).forEach(function(file) {
                    const fileItem = $('<div class="file-item"></div>');
                    const fileName = $('<span class="file-name"></span>').text(file.name);
                    const fileSize = $('<span class="file-size"></span>').text(formatFileSize(file.size));
                    const filePreview = $('<div class="file-preview"></div>');
                    
                    // Create preview for images
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = $('<img>').attr('src', e.target.result);
                            filePreview.append(img);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        filePreview.append('<span class="file-icon">📄</span>');
                    }
                    
                    fileItem.append(filePreview);
                    fileItem.append(fileName);
                    fileItem.append(fileSize);
                    $fileList.append(fileItem);
                });
                
                // Update upload text
                const $uploadText = $(formConfig.formId).find('#customDumpsterImages .file-upload-text span');
                $uploadText.text(files.length === 1 ? '1 file selected' : files.length + ' files selected');
            } else {
                // Reset upload text
                const $uploadText = $(formConfig.formId).find('#customDumpsterImages .file-upload-text span');
                $uploadText.text('Upload Images');
            }
        });

        // Helper function to format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

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

        // --- STEP 5: CONTENT FIELDS DYNAMIC LOGIC ---
        // Handle "Do you want to provide website content at this time?"
        form.on('change', 'input[name="provide_content_now"]', function() {
            const showContentFields = $(this).val() === 'Yes';
            const $contentFields = $('#contentFields');
            const $template4Only = $contentFields.find('.template4-only');
            
            if (showContentFields) {
                $contentFields.slideDown();
                
                // Check which template is selected
                const selectedTemplate = form.find('input[name="website_template_selection"]:checked').val();
                
                // Show template4-only section only if Template 4 is selected
                if (selectedTemplate === 'template4') {
                    $template4Only.slideDown();
                } else {
                    $template4Only.slideUp();
                }
            } else {
                $contentFields.slideUp();
            }
        });

        // Handle template selection change (if user goes back to Step 4 and changes template)
        form.on('change', 'input[name="website_template_selection"]', function() {
            // Only update if contentFields is visible
            if ($('#contentFields').is(':visible')) {
                const selectedTemplate = $(this).val();
                const $template4Only = $('#contentFields .template4-only');
                
                if (selectedTemplate === 'template4') {
                    $template4Only.slideDown();
                } else {
                    $template4Only.slideUp();
                }
            }
        });

        // Handle tagline question - show/hide tagline input field
        form.on('change', 'input[name="provide_tagline"]', function() {
            const showTaglineField = $(this).val() === 'Yes';
            if (showTaglineField) {
                $('#taglineField').slideDown();
                $('#taglineField input[name="company_tagline"]').prop('required', true);
            } else {
                $('#taglineField').slideUp();
                $('#taglineField input[name="company_tagline"]').prop('required', false);
            }
        });

        // Handle FAQs question - show/hide FAQ input field (Template 4 only)
        form.on('change', 'input[name="provide_faqs"]', function() {
            const showFaqField = $(this).val() === 'Yes';
            if (showFaqField) {
                $('#faqField').slideDown();
                $('#faqField textarea[name="company_faqs"]').prop('required', true);
            } else {
                $('#faqField').slideUp();
                $('#faqField textarea[name="company_faqs"]').prop('required', false);
            }
        });

        // Handle Benefits question - show/hide benefits input field (Template 4 only)
        form.on('change', 'input[name="provide_benefits"]', function() {
            const showBenefitsField = $(this).val() === 'Yes';
            if (showBenefitsField) {
                $('#benefitsField').slideDown();
                $('#benefitsField textarea[name="benefits_what_we_do"]').prop('required', true);
            } else {
                $('#benefitsField').slideUp();
                $('#benefitsField textarea[name="benefits_what_we_do"]').prop('required', false);
            }
        });

        // Handle footer question - show/hide footer input field
        form.on('change', 'input[name="provide_footer"]', function() {
            const showFooterField = $(this).val() === 'Yes';
            if (showFooterField) {
                $('#footerField').slideDown();
                $('#footerField input[name="website_footer"]').prop('required', true);
            } else {
                $('#footerField').slideUp();
                $('#footerField input[name="website_footer"]').prop('required', false);
            }
        });

        // --- STEP 7: RENTALS DYNAMIC FIELD LOGIC ---
        // Use document-level delegation scoped to this form to ensure handlers work

        // Handle dumpster color selection - show/hide custom images upload
        // Remove handler for this specific form before adding to prevent duplicates
        $(document).off('change', formConfig.formId + ' input[name="dumpster_color"]');
        $(document).on('change', formConfig.formId + ' input[name="dumpster_color"]', function() {
            const isCustom = $(this).val() === 'Custom';
            const $customImages = $(formConfig.formId).find('#customDumpsterImages');
            if (isCustom) {
                $customImages.css('display', '').slideDown();
                $customImages.find('input').prop('required', true);
            } else {
                $customImages.slideUp(function() {
                    $(this).css('display', 'none');
                });
                $customImages.find('input').prop('required', false);
            }
        });

        // Handle dumpster types checkboxes - show/hide corresponding sections
        // Remove handler for this specific form before adding to prevent duplicates
        $(document).off('change', formConfig.formId + ' input[name="dumpster_types[]"]');
        $(document).on('change', formConfig.formId + ' input[name="dumpster_types[]"]', function() {
            const dumpsterType = $(this).val();
            const isChecked = $(this).is(':checked');
            
            let sectionId = '';
            if (dumpsterType === 'Roll-Off') {
                sectionId = '#rollOffSection';
            } else if (dumpsterType === 'Hook-Lift') {
                sectionId = '#hookLiftSection';
            } else if (dumpsterType === 'Dump Trailers') {
                sectionId = '#dumpTrailerSection';
            }
            
            if (sectionId) {
                const $section = $(formConfig.formId).find(sectionId);
                if (isChecked) {
                    $section.css('display', '').slideDown();
                } else {
                    $section.slideUp(function() {
                        $(this).css('display', 'none');
                    });
                }
            }
        });

        // Handle services offered - show/hide junk removal section
        // Remove handler for this specific form before adding to prevent duplicates
        $(document).off('change', formConfig.formId + ' input[name="services_offered[]"]');
        $(document).on('change', formConfig.formId + ' input[name="services_offered[]"]', function() {
            const $form = $(formConfig.formId);
            const hasJunkRemoval = $form.find('input[name="services_offered[]"][value="Dumpster Rentals & Junk Removal"]').is(':checked');
            
            const $junkSection = $form.find('#junkRemovalSection');
            if (hasJunkRemoval) {
                $junkSection.css('display', '').slideDown();
                $junkSection.find('input[type="checkbox"]').prop('required', true);
            } else {
                $junkSection.slideUp(function() {
                    $(this).css('display', 'none');
                });
                $junkSection.find('input[type="checkbox"]').prop('required', false);
            }
        });

        // Handle "Add Dumpster" buttons for dynamic entries
        // Remove handler for this specific form before adding to prevent duplicates
        $(document).off('click', formConfig.formId + ' .add-dumpster-btn');
        $(document).on('click', formConfig.formId + ' .add-dumpster-btn', function(e) {
            e.preventDefault();
            const dumpsterType = $(this).data('type');
            const containerId = dumpsterType === 'roll-off' ? '#rollOffEntries' :
                               dumpsterType === 'hook-lift' ? '#hookLiftEntries' :
                               '#dumpTrailerEntries';
            
            const entryHtml = `
                <div class="dumpster-entry">
                    <div class="dumpster-entry-grid">
                        <div class="dumpster-field">
                            <label>Size *</label>
                            <input type="text" name="dumpster_${dumpsterType}_size[]" placeholder="e.g., 10 yd" required>
                        </div>
                        <div class="dumpster-field">
                            <label>Rental Period *</label>
                            <input type="text" name="dumpster_${dumpsterType}_period[]" placeholder="e.g., 1, 3, 7 days" required>
                        </div>
                        <div class="dumpster-field">
                            <label>Tons Allowed *</label>
                            <input type="text" name="dumpster_${dumpsterType}_tons[]" placeholder="e.g., 1 ton" required>
                        </div>
                        <div class="dumpster-field">
                            <label>Starting Price *</label>
                            <input type="text" name="dumpster_${dumpsterType}_price[]" placeholder="e.g., 399" required>
                        </div>
                    </div>
                    <button type="button" class="delete-dumpster-btn">Delete</button>
                </div>
            `;
            
            $(formConfig.formId).find(containerId).append(entryHtml);
        });

        // Handle delete dumpster entry
        // Remove handler for this specific form before adding to prevent duplicates
        $(document).off('click', formConfig.formId + ' .delete-dumpster-btn');
        $(document).on('click', formConfig.formId + ' .delete-dumpster-btn', function(e) {
            e.preventDefault();
            $(this).closest('.dumpster-entry').slideUp(300, function() {
                $(this).remove();
            });
        });

        // --- UI HELPERS ---
        function showProcessingScreen() {
            const processingHTML = `
                <div class="processing-overlay">
                    <div class="processing-content">
                        <div class="processing-spinner"></div>
                        <h2 class="processing-title">Processing Your Order</h2>
                        <p class="processing-message">Please wait while we create your website...</p>
                        
                        <div class="processing-progress">
                            <div class="processing-progress-bar" style="width: 0%;"></div>
                        </div>
                        
                        <div class="processing-steps">
                            <div class="processing-step" data-step="1">
                                <div class="processing-step-icon"></div>
                                <span>Validating your information</span>
                            </div>
                            <div class="processing-step" data-step="2">
                                <div class="processing-step-icon"></div>
                                <span>Creating your website</span>
                            </div>
                            <div class="processing-step" data-step="3">
                                <div class="processing-step-icon"></div>
                                <span>Setting up templates</span>
                            </div>
                            <div class="processing-step" data-step="4">
                                <div class="processing-step-icon"></div>
                                <span>Finalizing details</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(processingHTML);
            
            // Animate through the steps
            let currentProcessingStep = 0;
            const totalSteps = 4;
            
            function updateProcessingStep() {
                // Mark previous steps as completed
                $('.processing-step').each(function(index) {
                    if (index < currentProcessingStep) {
                        $(this).removeClass('active').addClass('completed');
                        $(this).find('.processing-step-icon').html('✓');
                    } else if (index === currentProcessingStep) {
                        $(this).addClass('active').removeClass('completed');
                        $(this).find('.processing-step-icon').html('');
                    } else {
                        $(this).removeClass('active completed');
                        $(this).find('.processing-step-icon').html('');
                    }
                });
                
                // Update progress bar
                const progress = (currentProcessingStep / totalSteps) * 100;
                $('.processing-progress-bar').css('width', progress + '%');
                
                // Move to next step
                if (currentProcessingStep < totalSteps) {
                    currentProcessingStep++;
                    setTimeout(updateProcessingStep, 800);
                }
            }
            
            // Start the animation
            setTimeout(updateProcessingStep, 500);
        }

        function hideProcessingScreen() {
            $('.processing-overlay').fadeOut(300, function() { $(this).remove(); });
        }
        
        // Restore form data on load (after all handlers are registered)
        restoreFormData();
        
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

