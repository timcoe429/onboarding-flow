<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the fast build form
 */
function docket_render_fast_build_form($form_data = array()) {
    // Extract form data passed from onboarding
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : '';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    ?>
    
    <div class="docket-fast-form" id="docketFastBuildForm">
        <!-- Clean Progress Bar -->
        <div class="docket-form-progress">
            <div class="docket-progress-track">
                <div class="docket-progress-fill" data-progress="12.5"></div>
            </div>
            <div class="docket-progress-dots">
                <span class="active" data-step="1">1</span>
                <span data-step="2">2</span>
                <span data-step="3">3</span>
                <span data-step="4">4</span>
                <span data-step="5">5</span>
                <span data-step="6">6</span>
                <span data-step="7">7</span>
                <span data-step="8">8</span>
            </div>
        </div>

        <form id="fastBuildForm" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr($plan_type); ?>">
            <input type="hidden" name="docket_management_type" value="<?php echo esc_attr($management_type); ?>">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">

            <!-- Step 1: Terms & WordPress Knowledge -->
            <div class="form-step active" data-step="1">
                <h2>Fast Build Website</h2>
                <p class="step-subtitle">Let's start by reviewing the terms and checking your WordPress experience</p>
                
                <!-- Terms Section -->
                <div class="terms-box mb-20">
                    <div class="terms-content">
                        <h4>Fast Build Terms & Conditions</h4>
                        <p>By proceeding with the Fast Build option, you understand:</p>
                        <ul>
                            <li>Your website will be ready in 3 days</li>
                            <li>Zero revisions before launch - customization is your responsibility</li>
                            <li>Stock content and images will be used</li>
                            <li>You'll need WordPress/Elementor knowledge to customize</li>
                            <li>Payment is due immediately to begin work</li>
                            <li>Changes after launch are charged at $175/hour</li>
                        </ul>
                        
                        <p class="terms-link">
                            <a href="#" onclick="showFullTerms(); return false;">View Full Terms</a>
                        </p>
                    </div>
                    
                    <label class="checkbox-card">
                        <input type="checkbox" name="accept_terms" value="accepted" required>
                        <span>I accept the terms & conditions</span>
                    </label>
                </div>
                
                <!-- WordPress Experience -->
                <div class="form-field">
                    <label>WordPress/Elementor Experience *</label>
                    <p class="field-note">How would you rate your experience?</p>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="wordpress_exp" value="Beginner" required>
                            <div class="radio-card-content">
                                <strong>Beginner</strong>
                                <span>I've never used WordPress/Elementor or only a few times</span>
                            </div>
                        </label>
                        
                        <label class="radio-card">
                            <input type="radio" name="wordpress_exp" value="Intermediate" required>
                            <div class="radio-card-content">
                                <strong>Intermediate</strong>
                                <span>I've used it enough to feel comfortable making edits</span>
                            </div>
                        </label>
                        
                        <label class="radio-card">
                            <input type="radio" name="wordpress_exp" value="Expert" required>
                            <div class="radio-card-content">
                                <strong>Expert</strong>
                                <span>I use it regularly and am very confident</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="notice-box" id="wpNotice" style="display: none;">
                    <p class="notice-text"></p>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 2: Contact & Business Info -->
            <div class="form-step" data-step="2">
                <h2>Contact & Business Information</h2>
                <p class="step-subtitle">This information will be displayed on your website</p>
                
                <div class="form-grid-2col">
                    <div class="form-field">
                        <label>Contact Name *</label>
                        <input type="text" name="name" autocomplete="name" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Contact Email *</label>
                        <input type="email" name="email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Name *</label>
                        <input type="text" name="business_name" autocomplete="organization" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Phone *</label>
                        <input type="tel" name="phone_number" autocomplete="tel" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Email *</label>
                        <input type="email" name="business_email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Address *</label>
                        <input type="text" name="business_address" autocomplete="street-address" placeholder="123 Main St, Denver, CO" required>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 3: Template Information -->
            <div class="form-step" data-step="3">
                <h2>Fast Build Template Information</h2>
                <p class="step-subtitle">Important information about your Fast Build template</p>
                
                <div class="info-box">
                    <h4>What's Included in Fast Build</h4>
                    
                    <div class="info-section">
                        <h5>Stock Content Only</h5>
                        <p>Your website will be built with placeholder content and stock images. You'll need to customize all text and images after launch.</p>
                    </div>

                    <div class="info-section">
                        <h5>No Revisions</h5>
                        <p>Fast Build includes zero revision rounds. What you see in the template preview is what you'll receive.</p>
                    </div>

                    <div class="info-section">
                        <h5>Self-Customization Required</h5>
                        <p>You'll receive WordPress/Elementor access to customize your site. Make sure you're comfortable with these tools.</p>
                    </div>

                    <div class="info-section">
                        <h5>3-Day Turnaround</h5>
                        <p>Your website will be ready to launch within 3 business days of payment and domain setup.</p>
                    </div>
                </div>
                
                <label class="checkbox-card">
                    <input type="checkbox" name="understand_fast_build" value="understood" required>
                    <span>I understand the Fast Build limitations and am ready to proceed</span>
                </label>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 4: Template Selection -->
            <div class="form-step" data-step="4">
                <h2>Choose Your Template</h2>
                <p class="step-subtitle">Select the design that best fits your business</p>
                
                <div class="template-grid">
                    <label class="template-card">
                        <input type="radio" name="website_template_selection" value="template1" required>
                        <div class="template-preview">
                            <img src="https://via.placeholder.com/300x200/1a73e8/ffffff?text=Template+1" alt="Template 1">
                            <span class="template-name">Template 1</span>
                        </div>
                    </label>
                    
                    <label class="template-card">
                        <input type="radio" name="website_template_selection" value="template2" required>
                        <div class="template-preview">
                            <img src="https://via.placeholder.com/300x200/34a853/ffffff?text=Template+2" alt="Template 2">
                            <span class="template-name">Template 2</span>
                        </div>
                    </label>
                    
                    <label class="template-card">
                        <input type="radio" name="website_template_selection" value="template3" required>
                        <div class="template-preview">
                            <img src="https://via.placeholder.com/300x200/ea4335/ffffff?text=Template+3" alt="Template 3">
                            <span class="template-name">Template 3</span>
                        </div>
                    </label>
                    
                    <label class="template-card">
                        <input type="radio" name="website_template_selection" value="template4" required>
                        <div class="template-preview">
                            <img src="https://via.placeholder.com/300x200/fbbc04/ffffff?text=Template+4" alt="Template 4">
                            <span class="template-name">Template 4</span>
                        </div>
                    </label>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 5: Service Areas & Blog Focus -->
            <div class="form-step" data-step="5">
                <h2>Service Areas</h2>
                <p class="step-subtitle">List up to 9 areas you service (cities, counties, regions)</p>
                
                <?php if ($plan_type === 'pro'): ?>
                <div class="form-field mb-30">
                    <label>Blog Content Focus *</label>
                    <p class="field-note">Since you selected the Pro plan, choose your blog content focus</p>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="blog_focus" value="Residential" required>
                            <div class="radio-card-content">Residential Focus</div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="blog_focus" value="Commercial" required>
                            <div class="radio-card-content">Commercial Focus</div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="blog_focus" value="Both" required>
                            <div class="radio-card-content">Mix of Both</div>
                        </label>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="service-areas-grid">
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                    <div class="service-area-field">
                        <label><?php echo $i; ?>.</label>
                        <input type="text" name="servicearea<?php echo $i; ?>" placeholder="City, State">
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 6: Branding -->
            <div class="form-step" data-step="6">
                <h2>Company Branding</h2>
                <p class="step-subtitle">Help us match your brand identity</p>
                
                <div class="form-field">
                    <label>Do you have a logo? *</label>
                    <div class="radio-inline">
                        <label>
                            <input type="radio" name="logo_question" value="Yes" required>
                            <span>Yes, I'll upload it</span>
                        </label>
                        <label>
                            <input type="radio" name="logo_question" value="No" required>
                            <span>No, use company name</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field" id="logoUpload" style="display: none;">
                    <label>Upload Logo *</label>
                    <div class="file-upload">
                        <input type="file" name="logo_files[]" accept="image/*" multiple>
                        <div class="file-upload-text">
                            <i class="fa fa-cloud-upload"></i>
                            <span>Click to upload or drag files here</span>
                            <small>Preferred: 300x300px PNG or JPG</small>
                        </div>
                    </div>
                </div>
                
                <div class="info-box mb-20">
                    <p><strong>Company Colors</strong></p>
                    <p>We need the exact HEX code of the colors you'd like us to use. You can use <a href="https://imagecolorpicker.com" target="_blank">this tool</a> to find exact HEX codes.</p>
                </div>
                
                <div class="form-grid-2col">
                    <div class="form-field">
                        <label>Primary Color (HEX) *</label>
                        <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="#00008B" required>
                        <small>Please provide a HEX code</small>
                    </div>
                    
                    <div class="form-field">
                        <label>Secondary Color (HEX)</label>
                        <input type="text" name="company_colors2" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="#FF6B6B">
                        <small>Optional second color</small>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 7: Rentals Info -->
            <div class="form-step" data-step="7">
                <h2>Rental Information</h2>
                <p class="step-subtitle">Tell us about your services (we'll use stock images and content)</p>
                
                <div class="form-field">
                    <label>Dumpster Colors *</label>
                    <p class="field-note">We have stock images for these colors</p>
                    <div class="color-grid">
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Black" required>
                            <span class="color-box" style="background: #000"></span>
                            <span>Black</span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Blue" required>
                            <span class="color-box" style="background: #1a73e8"></span>
                            <span>Blue</span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Green" required>
                            <span class="color-box" style="background: #34a853"></span>
                            <span>Green</span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Red" required>
                            <span class="color-box" style="background: #ea4335"></span>
                            <span>Red</span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Orange" required>
                            <span class="color-box" style="background: #fbbc04"></span>
                            <span>Orange</span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Grey" required>
                            <span class="color-box" style="background: #666"></span>
                            <span>Grey</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>Services Offered *</label>
                    <div class="checkbox-group">
                        <label class="checkbox-card">
                            <input type="checkbox" name="services_offered[]" value="Dumpster Rentals" checked>
                            <span>Dumpster Rentals</span>
                        </label>
                        <label class="checkbox-card">
                            <input type="checkbox" name="services_offered[]" value="Junk Removal">
                            <span>Junk Removal</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>Dumpster Types</label>
                    <div class="checkbox-group">
                        <label class="checkbox-card">
                            <input type="checkbox" name="dumpster_types[]" value="Roll-Off" class="dumpster-type">
                            <span>Roll-Off</span>
                        </label>
                        <label class="checkbox-card">
                            <input type="checkbox" name="dumpster_types[]" value="Hook-Lift" class="dumpster-type">
                            <span>Hook-Lift</span>
                        </label>
                        <label class="checkbox-card">
                            <input type="checkbox" name="dumpster_types[]" value="Dump Trailers" class="dumpster-type">
                            <span>Dump Trailers</span>
                        </label>
                    </div>
                </div>

                <div class="info-box">
                    <p><strong>Note:</strong> Fast Build uses stock pricing and sizes. You'll need to update these after launch.</p>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 8: Marketing -->
            <div class="form-step" data-step="8">
                <h2>Marketing & SEO</h2>
                <p class="step-subtitle">One last question</p>
                
                <div class="form-field">
                    <label>Are you working with an SEO/Marketing agency? *</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="Yes" required>
                            <div class="radio-card-content">
                                <strong>Yes</strong>
                                <span>I'll inform them about the website limitations</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="Soon" required>
                            <div class="radio-card-content">
                                <strong>Planning to</strong>
                                <span>I'll work with one in the future</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="No" required>
                            <div class="radio-card-content">
                                <strong>No</strong>
                                <span>Not using external marketing</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="Interested" required>
                            <div class="radio-card-content">
                                <strong>Interested in Docket's Services</strong>
                                <span>Tell me more about your marketing</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="submit" class="btn-submit">Complete Fast Build Order</button>
                </div>
            </div>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2>Fast Build Order Submitted!</h2>
            <p>Thank you! Your Fast Build website will be ready in 3 business days.</p>
            <p class="success-note">You'll receive a confirmation email shortly with next steps.</p>
        </div>
    </div>

    <script>
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
        
        // Form submission
        form.on('submit', function(e) {
            e.preventDefault();
            
            if (!validateStep(currentStep)) {
                return;
            }
            
            // Show loading
            form.addClass('form-loading');
            
            // Collect form data
            const formData = new FormData(this);
            formData.append('action', 'docket_submit_fast_build_form');
            formData.append('nonce', '<?php echo wp_create_nonce("docket_fast_build_nonce"); ?>');
            
            // Submit via AJAX
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('.docket-fast-form form').hide();
                        $('.docket-form-progress').hide();
                        $('.form-success').show();
                    } else {
                        alert('Error: ' + (response.data.message || 'Something went wrong'));
                        form.removeClass('form-loading');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Connection error. Please try again. Error: ' + error);
                    form.removeClass('form-loading');
                }
            });
        });
        
        // Show full terms
        window.showFullTerms = function() {
            // You can implement a modal or expand the terms here
            alert('Full terms would be displayed here');
        };
    });
    </script>
    <?php
}

/**
 * AJAX Handler for form submission
 */
add_action('wp_ajax_docket_submit_fast_build_form', 'docket_handle_fast_build_submission');
add_action('wp_ajax_nopriv_docket_submit_fast_build_form', 'docket_handle_fast_build_submission');

function docket_handle_fast_build_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_fast_build_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
// Prepare email content
$email_content = "<html><body style='font-family: Arial, sans-serif;'>";
$email_content .= "<h2>Fast Build Form Submission</h2>";
$email_content .= "<hr style='border: 1px solid #ccc;'><br>";

// Order Information
$email_content .= "<h3>Order Details</h3>";
$email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
$email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Plan Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . ucfirst($_POST['docket_plan_type']) . "</td></tr>";
$email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Build Type:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>Fast Build (3 days)</td></tr>";
$email_content .= "</table><br>";

// Contact Information
$email_content .= "<h3>Contact Information</h3>";
$email_content .= "<table style='width: 100%; border-collapse: collapse;'>";
$email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Name:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['name'] . "</td></tr>";
$email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['email'] . "</td></tr>";
$email_content .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Phone:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $_POST['phone_number'] . "</td></tr>";
$email_content .= "</table><br>";

// Continue formatting other sections...
$email_content .= "</body></html>";

// Update headers for HTML email
$headers = array(
    'Content-Type: text/html; charset=UTF-8',
    'From: Docket Onboarding <noreply@yourdocketonline.com>'
);
    
    // Handle file uploads if any
    if (!empty($_FILES)) {
        $email_content .= "\nFile Uploads:\n";
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                // Multiple files
                for ($i = 0; $i < count($file['name']); $i++) {
                    if (!empty($file['name'][$i])) {
                        $email_content .= "- " . $file['name'][$i] . "\n";
                    }
                }
            } else {
                // Single file
                if (!empty($file['name'])) {
                    $email_content .= "- " . $file['name'] . "\n";
                }
            }
        }
    }
    
    // Send email
    $to = 'tim@servicecore.com';
    $subject = 'Fast Build Form Submission - ' . sanitize_text_field($_POST['business_name']);
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    $sent = wp_mail($to, $subject, $email_content, $headers);
    
    if ($sent) {
        wp_send_json_success(array('message' => 'Form submitted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to send email'));
    }
}
