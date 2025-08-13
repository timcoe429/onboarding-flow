jQuery(document).ready(function($) {
    const form = $('#websiteVipForm');
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
        $('html, body').animate({ scrollTop: $('.docket-vip-form').offset().top - 50 }, 300);
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
        
        // Clear previous errors
        currentStepEl.find('.error').removeClass('error');
        currentStepEl.find('.field-error').remove();
        
        required.each(function() {
            const $field = $(this);
            const fieldType = $field.attr('type');
            const fieldName = $field.attr('name');
            const val = $field.val();
            const $formField = $field.closest('.form-field');
            
            if ($field.is(':radio')) {
                const name = $field.attr('name');
                checkedRadios[name] = checkedRadios[name] || $(`input[name="${name}"]:checked`).length > 0;
                if (!checkedRadios[name]) {
                    valid = false;
                    $field.closest('.radio-group, .radio-inline, .form-field').addClass('error');
                    const label = $formField.find('label').text().replace('*', '').trim();
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
                        group.append('<div class="field-error">Please select at least one option</div>');
                    }
                } else if (!$field.is(':checked')) {
                    valid = false;
                    $field.closest('.checkbox-card, .form-field').addClass('error');
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
        
        return valid;
    }
    
    // Remove error on change
    $(document).on('change input', '.error', function() {
        $(this).removeClass('error');
        $(this).closest('.error').removeClass('error');
        $(this).closest('.form-field').find('.field-error').remove();
    });
    
    // Preset color button functionality
    $(document).on('click', '.color-preset', function(e) {
        e.preventDefault();
        
        const color = $(this).data('color');
        
        // Remove selected class from all buttons in this group
        $(this).closest('.preset-colors').find('.color-preset').removeClass('selected');
        
        // Add selected class to clicked button
        $(this).addClass('selected');
        
        // Update the input field
        $('input[name="company_colors"]').val(color);
        $('input[name="company_colors"]').siblings('.color-picker').val(color);
        
        // Clear any validation errors
        $('input[name="company_colors"]').removeClass('error');
        $('input[name="company_colors"]').closest('.form-field').find('.field-error').remove();
    });
    
    // Content visibility toggles
    $('input[name="do_you_want_to_give_our_team_website_content_at_this_time"]').on('change', function() {
        if ($(this).val() === 'Yes') {
            $('#contentFields').slideDown();
            $('#contentFields input[type="radio"]').attr('required', true);
        } else {
            $('#contentFields').slideUp();
            $('#contentFields input').attr('required', false);
        }
    });
    
    // Conditional field toggles for content
    $('input[name="do_you_want_to_provide_a_company_tagline"]').on('change', function() {
        if ($(this).val() === 'Yes') {
            $('#taglineField').slideDown();
        } else {
            $('#taglineField').slideUp();
        }
    });
    
    $('input[name="do_you_want_to_provide_5_company_faqs"]').on('change', function() {
        if ($(this).val() === 'Yes') {
            $('#faqField').slideDown();
            $('#faqField textarea').attr('required', true);
        } else {
            $('#faqField').slideUp();
            $('#faqField textarea').attr('required', false);
        }
    });
    
    $('input[name="benefits_QA"]').on('change', function() {
        if ($(this).val() === 'Yes') {
            $('#benefitsField').slideDown();
            $('#benefitsField textarea').attr('required', true);
        } else {
            $('#benefitsField').slideUp();
            $('#benefitsField textarea').attr('required', false);
        }
    });
    
    $('input[name="website_footer"]').on('change', function() {
        if ($(this).val() === 'Yes') {
            $('#footerField').slideDown();
        } else {
            $('#footerField').slideUp();
        }
    });
    
    // Logo upload toggle
    $('input[name="logo_question"]').on('change', function() {
        if ($(this).val().includes('Yes')) {
            $('#logoUpload').slideDown();
            $('#logoUpload input').attr('required', true);
        } else {
            $('#logoUpload').slideUp();
            $('#logoUpload input').attr('required', false);
        }
    });
    
    // Font toggle
    $('input[name="do_you_want_to_provide_a_font_for_your_h1_+_h2_areas_on_your_website"]').on('change', function() {
        if ($(this).val().includes('Yes')) {
            $('#fontField').slideDown();
            $('#fontField input').attr('required', true);
        } else {
            $('#fontField').slideUp();
            $('#fontField input').attr('required', false);
        }
    });
    
    // Dumpster color toggle
    $('input[name="dumpster_stock_image_color_selection"]').on('change', function() {
        if ($(this).val() === "I'll provide my own images") {
            $('#customDumpsterImages').slideDown();
            $('#customDumpsterImages input').attr('required', true);
        } else {
            $('#customDumpsterImages').slideUp();
            $('#customDumpsterImages input').attr('required', false);
        }
    });
    
    // Dumpster types toggle
    $('input[name="what_types_of_dumpsters_do_you_have[]"]').on('change', function() {
        const value = $(this).val();
        const isChecked = $(this).is(':checked');
        
        if (value === 'Roll-Off') {
            if (isChecked) {
                $('#rollOffSection').slideDown();
            } else {
                $('#rollOffSection').slideUp();
            }
        } else if (value === 'Hook-Lift') {
            if (isChecked) {
                $('#hookLiftSection').slideDown();
            } else {
                $('#hookLiftSection').slideUp();
            }
        } else if (value === 'Dump Trailers') {
            if (isChecked) {
                $('#dumpTrailerSection').slideDown();
            } else {
                $('#dumpTrailerSection').slideUp();
            }
        }
    });
    
    // Services offered toggle
    $('input[name="what_services_do_you_offer[]"]').on('change', function() {
        const junkRemovalChecked = $('input[name="what_services_do_you_offer[]"][value*="Junk Removal"]').is(':checked');
        
        if (junkRemovalChecked) {
            $('#junkRemovalSection').slideDown();
            $('#junkRemovalSection input[type="checkbox"]').attr('required', true);
        } else {
            $('#junkRemovalSection').slideUp();
            $('#junkRemovalSection input').attr('required', false);
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
    
    $('#dumpsterImageInput').on('change', function() {
        const files = this.files;
        const fileList = $('#dumpsterFileList');
        fileList.empty();
        
        if (files.length > 0) {
            fileList.append('<p style="margin-top: 10px; font-weight: 600;">Selected files:</p>');
            for (let i = 0; i < files.length; i++) {
                fileList.append(`<p style="margin: 5px 0; color: #6b7280; font-size: 14px;">• ${files[i].name}</p>`);
            }
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
    
    // Show processing screen
    function showProcessingScreen() {
        const processingHTML = `
            <div class="processing-overlay">
                <div class="processing-content">
                    <div class="processing-spinner"></div>
                    <h2 class="processing-title">Processing Your Website VIP Order</h2>
                    <p class="processing-message">We're setting up your premium website experience. This may take a moment...</p>
                    
                    <div class="processing-progress">
                        <div class="processing-progress-bar"></div>
                    </div>
                    
                    <div class="processing-steps">
                        <div class="processing-step" data-step="1">
                            <div class="processing-step-icon">1</div>
                            <span>Validating VIP form data</span>
                        </div>
                        <div class="processing-step" data-step="2">
                            <div class="processing-step-icon">2</div>
                            <span>Creating premium client profile</span>
                        </div>
                        <div class="processing-step" data-step="3">
                            <div class="processing-step-icon">3</div>
                            <span>Processing content & assets</span>
                        </div>
                        <div class="processing-step" data-step="4">
                            <div class="processing-step-icon">4</div>
                            <span>Setting up VIP project workspace</span>
                        </div>
                        <div class="processing-step" data-step="5">
                            <div class="processing-step-icon">5</div>
                            <span>Configuring premium support access</span>
                        </div>
                        <div class="processing-step" data-step="6">
                            <div class="processing-step-icon">6</div>
                            <span>Notifying VIP team</span>
                        </div>
                        <div class="processing-step" data-step="7">
                            <div class="processing-step-icon">7</div>
                            <span>Sending VIP welcome package</span>
                        </div>
                    </div>
                    
                    <div class="processing-reassurance">
                        <strong>VIP Treatment!</strong> We're preparing your premium website experience. This typically takes 60-120 seconds.
                    </div>
                    
                    <p class="processing-note">Crafting your exceptional website journey...</p>
                </div>
            </div>
        `;
        
        $('body').append(processingHTML);
        
        // Animate progress steps
        let currentProgressStep = 1;
        const totalSteps = 7;
        
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
                setTimeout(animateStep, 900 + Math.random() * 500); // Randomize timing slightly
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
        
        // Collect form data
        const formData = new FormData(this);
        formData.append('action', 'docket_submit_website_vip_form');
        formData.append('nonce', $('#websiteVipForm input[name="nonce"]').val());
        
        // Submit via AJAX
        $.ajax({
            url: docket_ajax.ajax_url,
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
                                        <h2 class="processing-title">VIP Order Submitted Successfully!</h2>
                                        <p class="processing-message">Redirecting to your premium client portal...</p>
                                    </div>
                                </div>
                            `;
                            $('body').append(successOverlay);
                            
                            setTimeout(() => {
                                window.location.href = response.data.redirect_url;
                            }, 1500);
                        } else {
                            // Fallback to showing success message
                            $('.docket-vip-form form').hide();
                            $('.docket-form-progress').hide();
                            $('.form-success').show();
                        }
                    } else {
                        alert('Error: ' + (response.data.message || 'Something went wrong'));
                    }
                }, 3000); // Longer for VIP processing
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
        // Create modal if it doesn't exist
        if ($('#termsModal').length === 0) {
            const modalHtml = `
                <div id="termsModal" class="terms-modal" style="display: none;">
                    <div class="terms-modal-overlay"></div>
                    <div class="terms-modal-content">
                        <div class="terms-modal-header">
                            <h3>Website Design & Development Terms & Conditions</h3>
                            <button class="terms-modal-close">&times;</button>
                        </div>
                        <div class="terms-modal-body">
                            ${getFullTermsContent()}
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            
            // Close modal events
            $('#termsModal .terms-modal-close, #termsModal .terms-modal-overlay').on('click', function() {
                $('#termsModal').fadeOut();
            });
        }
        
        $('#termsModal').fadeIn();
    };
    
    // Full terms content
    function getFullTermsContent() {
        return `
            <p>These are the standard terms and conditions for Website Design and Development and apply to all contracts and all work that has been undertaken by Docket for its clients.</p>
            
            <p>By stating "I agree" via email, or making Payments, you are confirming that you can access and read and agree to all of this agreement and consent to use of this electronic method of contract acceptance under the U.S. Electronic Signatures in Global and National Commerce Act (E-SIGN).</p>
            
            <h4>Development</h4>
            <p>This Web Design Project will be developed using the latest version of WordPress HTML5 with standard WordPress Elements, unless specified otherwise.</p>
            
            <h4>Browser Compatibility</h4>
            <p>Designing a website to fully work in multiple browsers (and browser versions & resolutions) can require considerable, extra effort. It could also involve creating multiple versions of code/pages. Docket represents and warrants that the website we design for the latest browser versions for:</p>
            <ul>
                <li>Microsoft Edge</li>
                <li>Google Chrome</li>
                <li>Firefox</li>
                <li>Safari</li>
            </ul>
            
            <h4>Our Fees and Deposits</h4>
            <p>The total fee payable under our proposal is due immediately upon you instructing us to proceed with the website design and development work. We reserve the right not to commence any work until the amount has been paid in full.</p>
            <p>The amount paid is only refundable if we have not fulfilled our obligations to deliver the work required under the agreement. The total paid is not refundable if the development work has been started and you terminate the contract or work through no fault of ours or if you accept ownership of the project transferred to you.</p>
            
            <h4>Supply of Materials</h4>
            <p>You must supply all materials and information required by us to complete the work in accordance with any agreed specification. Such materials may include but are not limited to, photographs, written copy, logos, and other printed material. Where there is any delay in supplying these materials to us which leads to a delay in the completion of work, we have the right to extend any previously agreed deadlines by a reasonable amount. All materials and information must be submitted before starting your project.</p>
            <p>Where you fail to supply materials, and that prevents the progress of the work, we have the right to invoice you for any part or parts of the work already completed.</p>
            
            <h4>Variations</h4>
            <p>We are pleased to offer you the opportunity to make revisions to the design up until the point that the website goes live. Once the website is live, any additional changes become the sole responsibility of the business owner. However, we have the right to limit the number of design proposals to a reasonable amount and may charge for additional designs if you make a change to the original design specification. Major deviations from the original specification will be charged at the flat rate of $175.00 per hour.</p>
            
            <h4>Project Delays and Client Liability</h4>
            <p>Any time frames or estimates that we give are contingent upon your full co-operation and complete and final content for the work pages. During development, there is a certain amount of feedback required in order to progress to subsequent phases. It is required that a single point of contact be appointed from your side and be made available on a daily basis in order to expedite the feedback process. Each party shall use reasonable efforts to notify the other party, in writing, of a delay. In the event that the client fails to respond within a 7-day period starting from the first contact attempt by Docket to the client, the website project will be considered abandoned and all obligations by Docket will be deemed terminated. A 20% fee of the proposed total project amount will be due to resume work on the project. Conditions beyond the reasonable control of the parties include, but are not limited to, natural disasters, acts of government after the date of the agreement, power failure, fire, flood, acts of God, labor disputes, riots, acts of war, terrorism and epidemics.</p>
            
            <h4>Approval of Work</h4>
            <p>On completion of the work, you will be notified and have the opportunity to review it. If we do not hear from you within 7 days of such notification, all items will be considered approved. Any of the work which has not been reported in writing to us as unsatisfactory within the 7-day review period will be deemed to have been approved. Once approved, or deemed approved, work cannot subsequently be rejected, and the contract will be deemed to have been completed.</p>
            
            <h4>Rejected Work</h4>
            <p>If you reject any of our work within the 7-day review period, or not approve subsequent work performed by us to remedy any points recorded as being unsatisfactory, and we, acting reasonably, consider that you have been unreasonable in any rejection of the work, we can elect to treat this contract as at an end and take measures to recover payment for the completed work.</p>
            
            <h4>Warranty by You As To Ownership of Intellectual Property Rights</h4>
            <p>You must obtain all necessary permissions and authorities in respect of the use of all copy, graphic images, registered company logos, names, and trademarks, or any other material that you supply to us to include in your website or web applications. You must indemnify us and hold us harmless from any claims or legal actions related to the content of your website.</p>
            
            <h4>Project Copyright</h4>
            <p>Rights to photos, graphics, work-up files, and computer programs are specifically not transferred to the Client and remain the property of their respective owners. Docket and its subcontractors retain the right to display graphics and other Web design elements as examples of their work in their respective portfolios.</p>
            
            <h4>Website Ownership</h4>
            <p>The entire website design, layout, and structure remain the exclusive property of Docket. If a client cancels their contract, they retain rights to their content and images, but not the website design or structure.</p>
            
            <h4>Website Content</h4>
            <p>Website content encompasses the textual, visual, or aural elements that users encounter on websites, including text, images, sounds, videos, and animations. Clients are responsible for providing all necessary content, such as text, images, graphics, forms, legal disclaimers, privacy policies, and terms and conditions, in a timely and electronic format. Docket will not be held accountable for delays or incomplete projects resulting from the client's inaction. While Docket may use temporary filler text or sample images to keep the project on track, these are sourced from royalty-free platforms. It remains the client's responsibility to ensure all content is authorized for use.</p>
            
            <h4>Search Engines</h4>
            <p>We do not guarantee any specific position in search engine results for your website. We perform basic search engine optimization according to current best practices.</p>
            
            <h4>Consequential Loss</h4>
            <p>We shall not be liable for any loss or damage, which you may suffer which is in any way attributable to any delay in performance or completion of our contract, however that delay arises.</p>
            
            <h4>Disclaimer</h4>
            <p>To the full extent permitted by law, all terms, conditions, warranties, undertakings, inducements or representations whether express, implied, statutory or otherwise (other than the express provisions of these terms and conditions) relating in any way to the services we provide to you are excluded. Without limiting the above, to the extent permitted by law, any liability of Docket under any term, condition, warranty or representation that by law cannot be excluded is, where permitted by law, limited at our option to the replacement, re-repair or re-supply of the services or the payment of the cost of the services that we were contracted to perform.</p>
            
            <h4>Subcontracting</h4>
            <p>We reserve the right to subcontract any services that we have agreed to perform for you as we see fit.</p>
            
            <h4>Non-Disclosure</h4>
            <p>We (and any subcontractors we engage) agree that we will not at any time disclose any of your confidential information to any third party.</p>
            
            <h4>Additional Expenses</h4>
            <p>You agree to reimburse us for any requested expenses which do not form part of our proposal including but not limited to the purchase of templates, third party software, stock photographs, fonts, domain name registration, web hosting or comparable expenses.</p>
            
            <h4>Governing Law</h4>
            <p>The agreement constituted by these terms and conditions and any proposal will be construed according to and is governed by the laws of the State of Colorado, United States. You and Docket submit to the non-exclusive jurisdiction of the state and federal courts located in Colorado in relation to any dispute arising under these terms and conditions or in relation to any services we perform for you.</p>
            
            <h4>E-Commerce</h4>
            <p>You are responsible for complying with all relevant laws relating to e-commerce, and to the full extent permitted by law will hold harmless, protect, and defend and indemnify Docket and its subcontractors from any claim, penalty, tax, tariff loss or damage arising from your or your clients' use of Internet electronic commerce.</p>
            
            <h4>Support Services</h4>
            <p>Following the completion of the site, Docket offers Support Services on a time and materials basis at Docket's standard rate. Support Services refer to commercially reasonable technical support and assistance to maintain and update the Deliverables, including correcting any errors or Deficiencies. These services do not encompass enhancements to the Project or other services outside the scope of the Proposal.</p>
            
            <h4>Client Access and Limitations</h4>
            <p>Clients are granted front-end access for content management. Backend access is limited to Docket exclusively. No additional plugins will be installed by Docket at any stage of the project. Unauthorized backend changes may result in additional charges or contract termination. Any requests for added functionality must be approved by Docket and may incur additional costs.</p>
            
            <h4>Post-Launch Services</h4>
            <p>After the website goes live, clients will be provided with backend access, allowing them to manage and make changes to the website as they see fit. However, if the client prefers Docket to manage and make these changes, Docket will provide these services at a flat rate of $175 per hour. This includes, but is not limited to, changes in design, content updates, and code alterations. These services will be separate from the initial website design and development, and will be subject to separate invoicing.</p>
            
            <h4>This Agreement</h4>
            <p>This legal agreement, the "Project Proposal," constitutes the sole agreement between Docket and the Client regarding this Web Design Project, which is now integrated into the Docket Software. Any additional work not specified in this agreement or any other amendment or modification to this agreement must be authorized by a written request signed or agreed via email by both Client and Docket. All prices specified in this contract, now included in the Docket Software, will be honored for 12 months after both parties agree to the contract. Continued services after that time will require a new agreement.</p>
            <p>The undersigned hereby agree to the terms, conditions, and stipulations of this agreement.</p>
            <p>This Agreement constitutes the entire understanding of the parties. Any changes or modifications thereto must be in writing and agreed by both parties.</p>
            
            <p><strong>Agreed To:</strong><br>
            By Client (Electronically Consent) – No Signature Needed</p>
            
            <p><strong>Duly Authorized:</strong><br>
            Docket - No Signature Needed, Valid only After Receipt of Payment</p>
        `;
    }
});
