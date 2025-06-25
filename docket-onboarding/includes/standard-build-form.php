<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the standard build form
 */
function docket_render_standard_build_form($form_data = array()) {
    // Extract form data passed from onboarding
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : '';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    ?>
    
    <div class="docket-standard-form" id="docketStandardBuildForm">
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

        <form id="standardBuildForm" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr($plan_type); ?>">
            <input type="hidden" name="docket_management_type" value="<?php echo esc_attr($management_type); ?>">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">

            <!-- Step 1: Terms & Conditions -->
            <div class="form-step active" data-step="1">
                <h2>Standard Website Build</h2>
                <p class="step-subtitle">Please review and accept the terms & conditions to start your website build</p>
                
                <div class="terms-box">
                    <div class="terms-content">
                        <h4>Website Design & Development Agreement</h4>
                        <p>These are the standard terms and conditions for Website Design and Development and apply to all contracts and all work undertaken by Docket.</p>
                        
                        <div class="terms-section">
                            <h5>Development</h5>
                            <p>This Web Design Project will be developed using the latest version of WordPress HTML5 with standard WordPress Elements.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Browser Compatibility</h5>
                            <p>We design for the latest browser versions of:</p>
                            <ul>
                                <li>Microsoft Edge</li>
                                <li>Google Chrome</li>
                                <li>Firefox</li>
                                <li>Safari</li>
                            </ul>
                        </div>

                        <div class="terms-section">
                            <h5>Our Fees and Deposits</h5>
                            <p>The total fee is due immediately upon instructing us to proceed. We reserve the right not to commence work until payment is received in full.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Supply of Materials</h5>
                            <p>You must supply all materials and information required to complete the work. All materials must be submitted before starting your project.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Variations</h5>
                            <p>Revisions are allowed until the website goes live. After launch, changes are the business owner's responsibility. Major deviations are charged at $175/hour.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Project Delays</h5>
                            <p>Time frames require your full cooperation. If no response within 7 days, the project is considered abandoned. A 20% fee applies to resume work.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Website Ownership</h5>
                            <p>The entire website design, layout, and structure remain the exclusive property of Docket.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Client Access</h5>
                            <p>Clients receive front-end access for content management. Backend access is limited to Docket exclusively.</p>
                        </div>

                        <div class="terms-section">
                            <h5>Post-Launch Services</h5>
                            <p>After launch, backend access is provided. Docket's management services are available at $175/hour.</p>
                        </div>

                        <p class="terms-link">
                            <a href="#" onclick="showFullTerms(); return false;">View Full Terms & Conditions</a>
                        </p>
                    </div>
                    
                    <label class="checkbox-card">
                        <input type="checkbox" name="accept_terms_conditions" value="I Accept" required>
                        <span>I Accept the Terms & Conditions</span>
                    </label>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 2: Contact & Business Info -->
            <div class="form-step" data-step="2">
                <h2>Contact & Business Information</h2>
                <p class="step-subtitle">Provide your contact information so we can reach out about your website</p>
                
                <div class="form-grid-2col">
                    <div class="form-field full-width">
                        <label>Contact First & Last Name *</label>
                        <input type="text" name="contact_name" autocomplete="name" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Contact Email Address *</label>
                        <input type="email" name="contact_email_address" autocomplete="email" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Name *</label>
                        <input type="text" name="business_name" autocomplete="organization" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Phone Number *</label>
                        <input type="tel" name="business_phone_number" autocomplete="tel" required>
                    </div>
                    
                    <div class="form-field">
                        <label>Business Email *</label>
                        <input type="email" name="business_email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-field full-width">
                        <label>Business Address *</label>
                        <textarea name="business_address" rows="2" placeholder="Examples: 3615 Delgany St Ste 1000, Denver, CO or Denver, CO" required></textarea>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 3: Website Template Information -->
            <div class="form-step" data-step="3">
                <h2>Website Template Information</h2>
                <p class="step-subtitle">You will now get to select your website template!</p>
                
                <div class="info-box">
                    <h4>Important Information About Your Template</h4>
                    
                    <div class="info-section">
                        <h5>Customized Website Information</h5>
                        <p>This is not a full custom website. It's a pre-built theme that we customize with your content and images. We do not do additional design work or custom requests not already built into the theme.</p>
                    </div>

                    <div class="info-section">
                        <h5>Sections & Pages Included</h5>
                        <p>The template preview shows exactly what's available — we do not add additional pages or sections beyond what is shown.</p>
                    </div>

                    <div class="info-section">
                        <h5>Revisions to Template</h5>
                        <p>We limit revisions to 1 round. You have 3 full days to review and request changes within scope. Your website is self-managed post-launch unless you upgrade to WebsiteVIP.</p>
                    </div>

                    <div class="info-section">
                        <h5>Additional Customizations</h5>
                        <p>Out-of-scope customizations are charged at $175/hour.</p>
                    </div>
                </div>
                
                <label class="checkbox-card">
                    <input type="checkbox" name="accept_webbuild_terms" value="I Understand" required>
                    <span>I understand the above clarifications on the process</span>
                </label>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>
<!-- Step 4: Select Template -->
            <div class="form-step" data-step="4">
                <h2>Select Your Website Template</h2>
                <p class="step-subtitle">Click on the images to preview each template</p>
                
                <div class="template-grid-2x2">
                    <div class="template-option">
                        <a href="https://dockethosting3.com/salesdemo1" target="_blank" class="template-preview-link">
                            <img src="https://yourdocketonline.com/wp-content/uploads/2025/05/Template-2-279x300.png" alt="Template 1">
                            <span class="template-preview-text">Click to View Template 1</span>
                        </a>
                        <label class="template-select">
                            <input type="radio" name="website_template_selection" value="template1" required>
                            <span>Select Template 1</span>
                        </label>
                    </div>
                    
                    <div class="template-option">
                        <a href="https://dockethosting3.com/salesdemo2/" target="_blank" class="template-preview-link">
                            <img src="https://yourdocketonline.com/wp-content/uploads/2025/05/Template-1-279x300.png" alt="Template 2">
                            <span class="template-preview-text">Click to View Template 2</span>
                        </a>
                        <label class="template-select">
                            <input type="radio" name="website_template_selection" value="template2" required>
                            <span>Select Template 2</span>
                        </label>
                    </div>
                    
                    <div class="template-option">
                        <a href="https://dockethosting3.com/salesdemo3" target="_blank" class="template-preview-link">
                            <img src="https://yourdocketonline.com/wp-content/uploads/2025/05/Template-3-279x300.png" alt="Template 3">
                            <span class="template-preview-text">Click to View Template 3</span>
                        </a>
                        <label class="template-select">
                            <input type="radio" name="website_template_selection" value="template3" required>
                            <span>Select Template 3</span>
                        </label>
                    </div>
                    
                    <div class="template-option">
                        <a href="https://dockethosting3.com/salesdemo4" target="_blank" class="template-preview-link">
                            <img src="https://yourdocketonline.com/wp-content/uploads/2025/05/Template-4-279x300.png" alt="Template 4">
                            <span class="template-preview-text">Click to View Template 4</span>
                        </a>
                        <label class="template-select">
                            <input type="radio" name="website_template_selection" value="template4" required>
                            <span>Select Template 4</span>
                        </label>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 5: Website Content -->
            <div class="form-step" data-step="5">
                <h2>Website Content Information</h2>
                <p class="step-subtitle">Customize your template with content specific to your business</p>
                
                <div class="info-box mb-30">
                    <p><strong>We want to give you the option to customize your template with content geared specifically to your business.</strong></p>
                    <p>If you'd rather suggest edits to the stock content during review, or customize post-launch, no worries!</p>
                </div>

                <div class="form-field">
                    <label>Do you want to provide website content at this time? *</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="provide_content_now" value="Yes" required>
                            <div class="radio-card-content">
                                <strong>Yes</strong>
                                <span>I'll provide custom content now</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="provide_content_now" value="No" required>
                            <div class="radio-card-content">
                                <strong>No</strong>
                                <span>Use all stock content for my website draft</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Content Fields (shown conditionally) -->
                <div id="contentFields" style="display: none;">
                    <!-- Company Tagline -->
                    <div class="form-field">
                        <label>Do you want to provide a company tagline? *</label>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="provide_tagline" value="Yes">
                                <span>Yes</span>
                            </label>
                            <label>
                                <input type="radio" name="provide_tagline" value="No">
                                <span>No - I'm okay with blank or stock content</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-field" id="taglineField" style="display: none;">
                        <label>Company Tagline (used on Home page)</label>
                        <input type="text" name="company_tagline" maxlength="65">
                    </div>

                    <!-- FAQs -->
                    <div class="form-field">
                        <label>Do you want to provide 5 company FAQ's? *</label>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="provide_faqs" value="Yes">
                                <span>Yes</span>
                            </label>
                            <label>
                                <input type="radio" name="provide_faqs" value="No">
                                <span>No - use stock FAQ's</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-field" id="faqField" style="display: none;">
                        <label>Company FAQ's (5) *</label>
                        <textarea name="company_faqs" rows="6" placeholder="Please provide 5 FAQ's. Include both the question AND the answer."></textarea>
                    </div>

                    <!-- Benefits -->
                    <div class="form-field">
                        <label>Do you want to provide 5 Benefits/What We Do Q+A's? *</label>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="provide_benefits" value="Yes">
                                <span>Yes</span>
                            </label>
                            <label>
                                <input type="radio" name="provide_benefits" value="No">
                                <span>No - use stock content</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-field" id="benefitsField" style="display: none;">
                        <label>Benefits/What We Do (5) *</label>
                        <textarea name="benefits_what_we_do" rows="6" placeholder="Please provide 5 Benefits/What We Do's. Include both the question AND the answer."></textarea>
                    </div>

                    <!-- Footer -->
                    <div class="form-field">
                        <label>Do you want to provide a company summary for website footer? *</label>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="provide_footer" value="Yes">
                                <span>Yes</span>
                            </label>
                            <label>
                                <input type="radio" name="provide_footer" value="No">
                                <span>No - use stock content</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-field" id="footerField" style="display: none;">
                        <label>Company Summary - Website Footer</label>
                        <input type="text" name="website_footer" maxlength="65">
                    </div>
                </div>

                <!-- PRO Plan Blog Focus -->
                <?php if ($plan_type === 'pro'): ?>
                <div class="form-field">
                    <label>Select the focus of your 4 blogs *</label>
                    <p class="field-note">These blogs help with content marketing and cast a wider net for potential customers.</p>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="blog_focus" value="Residential Dumpster Rentals" required>
                            <div class="radio-card-content">Residential Dumpster Rentals</div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="blog_focus" value="Commercial Dumpster Rentals" required>
                            <div class="radio-card-content">Commercial Dumpster Rentals</div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="blog_focus" value="Mix of Both" required>
                            <div class="radio-card-content">Mix of Both</div>
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Service Areas -->
                <div class="form-field">
                    <label>What are the 9 main areas you service?</label>
                    <p class="field-note">These can be cities, counties, regions, etc. Include city name AND state abbreviation (e.g., Denver, CO)</p>
                    <div class="service-areas-grid">
                        <?php for ($i = 1; $i <= 9; $i++): ?>
                        <div class="service-area-field">
                            <label><?php echo $i; ?>.</label>
                            <input type="text" name="servicearea<?php echo $i; ?>" placeholder="City, State">
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>
<!-- Step 6: Company Branding -->
            <div class="form-step" data-step="6">
                <h2>Company Branding</h2>
                <p class="step-subtitle">Help us match your brand identity</p>
                
                <div class="form-field">
                    <label>Do you have a logo you'd like to use? *</label>
                    <div class="radio-inline">
                        <label>
                            <input type="radio" name="logo_question" value="Yes" required>
                            <span>Yes - I will provide it</span>
                        </label>
                        <label>
                            <input type="radio" name="logo_question" value="No" required>
                            <span>No - Use the name of my company instead</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field" id="logoUpload" style="display: none;">
                    <label>Logo File(s) *</label>
                    <div class="file-upload">
                        <input type="file" name="logo_files[]" accept="image/*" multiple>
                        <div class="file-upload-text">
                            <i class="fa fa-upload"></i>
                            <span>Click to upload or drag files here</span>
                            <small>Preferred size: 300px x 300px or similar dimensions</small>
                        </div>
                    </div>
                </div>
                
                <div class="info-box mb-20">
                    <p><strong>Company Colors</strong></p>
                    <p>We need the exact HEX code of the colors you'd like us to use. You can use <a href="https://imagecolorpicker.com" target="_blank">this tool</a> to find exact HEX codes.</p>
                </div>
                
                <div class="form-grid-2col">
                    <div class="form-field">
                        <label>Company Color 1 HEX Code *</label>
                        <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" required>
                        <small>Please provide a HEX code</small>
                    </div>
                    
                    <div class="form-field">
                        <label>Company Color 2 HEX Code</label>
                        <input type="text" name="company_colors2" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B">
                        <small>Optional second color</small>
                    </div>
                </div>
                
                <div class="info-box mb-20">
                    <p><strong>Google Fonts</strong></p>
                    <p>We can use a Google Font for your Headings (H1 and H2). View available fonts <a href="https://fonts.google.com" target="_blank">here</a>.</p>
                </div>
                
                <div class="form-field">
                    <label>Do you want to provide a font for H1 + H2 areas? *</label>
                    <div class="radio-inline">
                        <label>
                            <input type="radio" name="provide_font" value="Yes" required>
                            <span>Yes - I will provide a Google Font Name</span>
                        </label>
                        <label>
                            <input type="radio" name="provide_font" value="No" required>
                            <span>No - Use the default font for my template</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field" id="fontField" style="display: none;">
                    <label>Font Name *</label>
                    <input type="text" name="font_name" placeholder="e.g., Roboto, Open Sans, etc.">
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 7: Rentals Information -->
            <div class="form-step" data-step="7">
                <h2>Rentals Information</h2>
                <p class="step-subtitle">Tell us about your rental services</p>
                
                <div class="form-grid-3col">
                    <div class="form-field">
                        <label>What color are your dumpsters? *</label>
                        <p class="field-note">We provide stock images for: Black, Grey, Green, Red, Orange, and Blue</p>
                    </div>
                    
                    <div class="form-field">
                        <div class="radio-group compact">
                            <label><input type="radio" name="dumpster_color" value="Black" required><span>Black</span></label>
                            <label><input type="radio" name="dumpster_color" value="Blue" required><span>Blue</span></label>
                            <label><input type="radio" name="dumpster_color" value="Grey" required><span>Grey</span></label>
                            <label><input type="radio" name="dumpster_color" value="Orange" required><span>Orange</span></label>
                            <label><input type="radio" name="dumpster_color" value="Red" required><span>Red</span></label>
                            <label><input type="radio" name="dumpster_color" value="Green" required><span>Green</span></label>
                            <label><input type="radio" name="dumpster_color" value="Custom" required><span>I'll provide images</span></label>
                        </div>
                    </div>
                    
                    <div class="form-field" id="customDumpsterImages" style="display: none;">
                        <label>Upload Dumpster Images *</label>
                        <div class="file-upload small">
                            <input type="file" name="dumpster_images[]" accept="image/*" multiple>
                            <div class="file-upload-text">
                                <span>Upload Images</span>
                                <small>600px x 400px preferred</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>What services do you offer? *</label>
                    <div class="checkbox-group">
                        <label class="checkbox-card">
                            <input type="checkbox" name="services_offered[]" value="Just Dumpster Rentals">
                            <span>Just Dumpster Rentals</span>
                        </label>
                        <label class="checkbox-card">
                            <input type="checkbox" name="services_offered[]" value="Dumpster Rentals & Junk Removal">
                            <span>Dumpster Rentals & Junk Removal</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>What types of dumpsters do you have?</label>
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
                    <h4>Dumpster Rental Information for Your Website</h4>
                    <ul>
                        <li><strong>Rental Sizes:</strong> e.g., 10 yd, 20 yd, 30 yd</li>
                        <li><strong>Rental Periods:</strong> e.g., 1, 3, and 7 Day Rentals</li>
                        <li><strong>Tons Allowed:</strong> We'll show lowest tonnage with '+' (e.g., 2+ Tons)</li>
                        <li><strong>Pricing:</strong> We'll show starting price with '+' (e.g., $399+)</li>
                    </ul>
                </div>

                <!-- Dynamic Rental Info Sections -->
                <div id="rollOffSection" class="rental-section" style="display: none;">
                    <h4><i class="fa fa-dumpster"></i> Roll-Off Dumpster Information</h4>
                    <div class="form-grid-2col">
                        <div class="form-field">
                            <label>Rental Sizes</label>
                            <textarea name="roll_sizes" rows="2" placeholder="e.g., 10 yd, 20 yd, 30 yd"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Rental Periods</label>
                            <textarea name="roll_rentalperiods" rows="2" placeholder="e.g., 1, 3, and 7 Day Rentals"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Tons Allowed</label>
                            <textarea name="roll_tons" rows="2" placeholder="e.g., 2 tons, 3 tons"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Starting Prices</label>
                            <textarea name="roll_startingprice" rows="2" placeholder="e.g., $299"></textarea>
                        </div>
                    </div>
                </div>

                <div id="hookLiftSection" class="rental-section" style="display: none;">
                    <h4><i class="fa fa-truck-loading"></i> Hook-Lift Dumpster Information</h4>
                    <div class="form-grid-2col">
                        <div class="form-field">
                            <label>Rental Sizes</label>
                            <textarea name="hook_rentalsizes" rows="2"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Rental Periods</label>
                            <textarea name="hook_rentalperiods" rows="2"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Tons Allowed</label>
                            <textarea name="hook_rentaltons" rows="2"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Starting Prices</label>
                            <textarea name="hook_price" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div id="dumpTrailerSection" class="rental-section" style="display: none;">
                    <h4><i class="fa fa-caravan"></i> Dump Trailer Information</h4>
                    <div class="form-grid-2col">
                        <div class="form-field">
                            <label>Rental Sizes</label>
                            <textarea name="dump_trailersize" rows="2"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Rental Periods</label>
                            <textarea name="dump_trailerrentals" rows="2"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Tons Allowed</label>
                            <textarea name="dump_trailertons" rows="2"></textarea>
                        </div>
                        <div class="form-field">
                            <label>Starting Prices</label>
                            <textarea name="dump_trailerprice" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Junk Removal Section -->
                <div id="junkRemovalSection" style="display: none;">
                    <div class="info-box">
                        <h4>Junk Removal Information</h4>
                        <ul>
                            <li><strong>No Pricing:</strong> Customers will click "Get Estimate" for quotes</li>
                            <li><strong>Types:</strong> Select residential, commercial, or both</li>
                        </ul>
                    </div>
                    
                    <div class="form-field">
                        <label>What junk removal services do you offer? *</label>
                        <div class="checkbox-group">
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Hoarding Cleanouts">
                                <span>Residential - Hoarding Cleanouts</span>
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Bagsters/Junk Bags">
                                <span>Residential - Bagsters/Junk Bags</span>
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - By the Truckload">
                                <span>Residential - By the Truckload</span>
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Single-Item Disposal">
                                <span>Residential - Single-Item Disposal</span>
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Construction Debris Removal">
                                <span>Commercial - Construction Debris</span>
                            </label>
                            
                            
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Bagsters/Junk Bags">
                                <span>Commercial - Bagsters/Junk Bags</span>
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - By the Truckload">
                                <span>Commercial - By the Truckload</span>
                            </label>
                            <label class="checkbox-card">
                                <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Single-Item Disposal">
                                <span>Commercial - Single-Item Disposal</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

    <style>
    /* Clean, Modern Form Styles */
    .docket-standard-form {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Progress Bar */
    .docket-form-progress {
        margin-bottom: 30px;
    }

    .docket-progress-track {
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .docket-progress-fill {
        height: 100%;
        background: #185fb0;
        transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        width: 12.5%;
    }

    .docket-progress-dots {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 10px;
    }

    .docket-progress-dots span {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .docket-progress-dots span.active {
        background: #185fb0;
        color: white;
        transform: scale(1.15);
    }

    .docket-progress-dots span.completed {
        background: #7eb10f;
        color: white;
    }

    /* Form Steps */
    .form-step {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .form-step.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-step h2 {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .step-subtitle {
        color: #6b7280;
        font-size: 15px;
        margin-bottom: 24px;
    }

    /* Utility Classes */
    .mb-20 { margin-bottom: 20px; }
    .mb-30 { margin-bottom: 30px; }

</style>

    <!-- Step 8: Company Marketing -->
            <div class="form-step" data-step="8">
                <h2>Company Marketing</h2>
                <p class="step-subtitle">Final questions about your marketing needs</p>
                
                <div class="form-field">
                    <label>Are you currently working with an SEO or Marketing agency? *</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="Yes" required>
                            <div class="radio-card-content">
                                <strong>Yes</strong>
                                <span>I'll make sure they know about the plugin and back-end access limitations</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="Soon" required>
                            <div class="radio-card-content">
                                <strong>I will be soon</strong>
                                <span>I'm planning on working with an external agency in the future</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="No" required>
                            <div class="radio-card-content">
                                <strong>No</strong>
                                <span>Not planning to use external marketing</span>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="marketing_agency" value="Interested" required>
                            <div class="radio-card-content">
                                <strong>No - but interested</strong>
                                <span>I'd like information on Docket's digital marketing services</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>Social Media Links</label>
                    <p class="field-note">Please provide the exact URLs for your social media profiles</p>
                    <div class="social-media-grid">
                        <div class="form-field">
                            <label>Facebook</label>
                            <input type="url" name="facebook" placeholder="https://www.facebook.com/YourPage">
                        </div>
                        <div class="form-field">
                            <label>Instagram</label>
                            <input type="url" name="instagram" placeholder="https://www.instagram.com/YourProfile">
                        </div>
                        <div class="form-field">
                            <label>X (Twitter)</label>
                            <input type="url" name="twitter" placeholder="https://www.x.com/YourProfile">
                        </div>
                        <div class="form-field">
                            <label>YouTube</label>
                            <input type="url" name="YouTube" placeholder="https://www.youtube.com/@YourChannel">
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>Any reviews or testimonials you'd like to add?</label>
                    <textarea name="reviews_testimonials" rows="4" placeholder="Please add both the review and the first name and last initial of the reviewer. We cannot pull reviews directly from Google or other platforms."></textarea>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="submit" class="btn-submit">Submit Order</button>
                </div>
            </div>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2>Order Submitted!</h2>
            <p>Thank you! We'll start building your website right away.</p>
            <p class="success-note">You'll receive a confirmation email shortly with next steps.</p>
        </div>
    </div>

    <!-- Continue CSS Styles -->
    <style>
    /* Form Fields */
    .form-field {
        margin-bottom: 18px;
    }

    .form-field label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-field input[type="text"],
    .form-field input[type="email"],
    .form-field input[type="tel"],
    .form-field input[type="url"],
    .form-field textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .form-field textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-field input:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: #185fb0;
        box-shadow: 0 0 0 3px rgba(24, 95, 176, 0.1);
    }

    .form-field small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
    }

    .field-note {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 8px;
    }

    /* Grid Layouts */
    .form-grid-2col {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 20px;
    }

    .form-grid-3col {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        align-items: start;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .service-areas-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .service-area-field {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .service-area-field label {
        font-weight: 600;
        color: #6b7280;
        margin: 0;
    }

    .social-media-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    /* Radio Cards */
    .radio-card {
        display: block;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .radio-card input {
        position: absolute;
        opacity: 0;
    }

    .radio-card-content {
        display: block;
        padding: 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        transition: all 0.2s ease;
    }

    .radio-card:hover .radio-card-content {
        border-color: #d1d5db;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .radio-card input:checked + .radio-card-content {
        border-color: #185fb0;
        background: #eff6ff;
    }

    .radio-card-content strong {
        display: block;
        font-size: 15px;
        color: #111827;
        margin-bottom: 2px;
    }

    .radio-card-content span {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
    }

    /* Inline Radio */
    .radio-inline {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .radio-inline label {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .radio-inline input {
        margin-right: 6px;
    }

    /* Compact Radio */
    .radio-group.compact label {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        margin-bottom: 8px;
    }

    /* Checkbox Cards */
    .checkbox-card {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .checkbox-card:hover {
        border-color: #d1d5db;
        background: #f9fafb;
    }

    .checkbox-card input {
        margin-right: 10px;
    }

    .checkbox-card input:checked + span {
        font-weight: 600;
        color: #111827;
    }

    /* Terms Box */
    .terms-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .terms-content {
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 16px;
    }

    .terms-content h4 {
        margin-bottom: 10px;
        color: #111827;
        font-size: 16px;
    }

    .terms-section {
        margin-bottom: 16px;
    }

    .terms-section h5 {
        margin-bottom: 8px;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }

    .terms-section p,
    .terms-section ul {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .terms-section ul {
        margin-left: 20px;
    }

    .terms-link {
        text-align: center;
        margin-top: 12px;
    }

    /* Info Box */
    .info-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .info-box h4 {
        color: #111827;
        font-size: 15px;
        margin-bottom: 10px;
    }

    .info-section {
        margin-bottom: 12px;
    }

    .info-section:last-child {
        margin-bottom: 0;
    }

    .info-section h5 {
        color: #374151;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-section p {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .info-box ul {
        margin-left: 20px;
        color: #6b7280;
        font-size: 13px;
    }

    .info-box li {
        margin-bottom: 4px;
    }

    /* Template Grid */
    .template-grid-2x2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .template-option {
        text-align: center;
    }

    .template-preview-link {
        display: block;
        border: 3px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }

    .template-preview-link:hover {
        border-color: #185fb0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .template-preview-link img {
        width: 100%;
        height: auto;
        display: block;
    }

    .template-preview-text {
        display: block;
        padding: 8px;
        background: #f9fafb;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
    }

    .template-select {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .template-select input {
        margin-right: 6px;
    }

    /* File Upload */
    .file-upload {
        position: relative;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 30px 20px;
        text-align: center;
        background: #f9fafb;
        transition: all 0.2s ease;
    }

    .file-upload.small {
        padding: 15px 10px;
    }

    .file-upload:hover {
        border-color: #185fb0;
        background: #eff6ff;
    }

    .file-upload input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-upload-text i {
        font-size: 36px;
        color: #9ca3af;
        display: block;
        margin-bottom: 10px;
    }

    .file-upload-text span {
        display: block;
        color: #374151;
        font-weight: 600;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .file-upload-text small {
        color: #6b7280;
        font-size: 12px;
    }

    /* Rental Sections */
    .rental-section {
        background: #f9fafb;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .rental-section h4 {
        color: #111827;
        font-size: 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rental-section h4 i {
        color: #185fb0;
    }

    /* Navigation */
    .form-nav {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-prev,
    .btn-next,
    .btn-submit {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-prev {
        background: white;
        color: #374151;
        border: 2px solid #e5e7eb;
    }

    .btn-prev:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .btn-next,
    .btn-submit {
        background: #185fb0;
        color: white;
        margin-left: auto;
    }

    .btn-next:hover,
    .btn-submit:hover {
        background: #1455a0;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(24, 95, 176, 0.2);
    }

    .btn-submit {
        background: #7eb10f;
    }

    .btn-submit:hover {
        background: #6fa000;
        box-shadow: 0 4px 8px rgba(126, 177, 15, 0.2);
    }

    /* Success Screen */
    .form-success {
        text-align: center;
        padding: 50px 20px;
    }

    .success-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 20px;
        background: #7eb10f;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: bold;
    }

    .form-success h2 {
        font-size: 28px;
        color: #111827;
        margin-bottom: 10px;
    }

    .form-success p {
        color: #6b7280;
        font-size: 16px;
        margin-bottom: 8px;
    }

    .success-note {
        font-size: 14px;
        margin-top: 20px;
    }

    /* Mobile Responsive */
    @media (max-width: 640px) {
        .docket-standard-form {
            padding: 16px;
        }

        .form-step h2 {
            font-size: 22px;
        }

        .form-grid-2col,
        .form-grid-3col,
        .template-grid-2x2,
        .social-media-grid {
            grid-template-columns: 1fr;
        }

        .service-areas-grid {
            grid-template-columns: 1fr;
        }

        .radio-inline {
            flex-direction: column;
            gap: 10px;
        }

        .docket-progress-dots span {
            width: 24px;
            height: 24px;
            font-size: 11px;
        }

        .form-nav {
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-prev,
        .btn-next,
        .btn-submit {
            flex: 1;
            min-width: 100px;
        }
    }

    /* Loading State */
    .form-loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Error states */
    .form-field.error input,
    .form-field.error textarea {
        border-color: #ef4444;
    }

    .error .radio-group,
    .error .checkbox-group {
        outline: 2px solid #ef4444;
        outline-offset: 2px;
        border-radius: 8px;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        const form = $('#standardBuildForm');
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
            $('html, body').animate({ scrollTop: $('.docket-standard-form').offset().top - 50 }, 300);
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
        
        // Content visibility toggles
        $('input[name="provide_content_now"]').on('change', function() {
            if ($(this).val() === 'Yes') {
                $('#contentFields').slideDown();
                $('#contentFields input[type="radio"]').attr('required', true);
            } else {
                $('#contentFields').slideUp();
                $('#contentFields input').attr('required', false);
            }
        });
        
        // Conditional field toggles
        $('input[name="provide_tagline"]').on('change', function() {
            if ($(this).val() === 'Yes') {
                $('#taglineField').slideDown();
            } else {
                $('#taglineField').slideUp();
            }
        });
        
        $('input[name="provide_faqs"]').on('change', function() {
            if ($(this).val() === 'Yes') {
                $('#faqField').slideDown();
                $('#faqField textarea').attr('required', true);
            } else {
                $('#faqField').slideUp();
                $('#faqField textarea').attr('required', false);
            }
        });
        
        $('input[name="provide_benefits"]').on('change', function() {
            if ($(this).val() === 'Yes') {
                $('#benefitsField').slideDown();
                $('#benefitsField textarea').attr('required', true);
            } else {
                $('#benefitsField').slideUp();
                $('#benefitsField textarea').attr('required', false);
            }
        });
        
        $('input[name="provide_footer"]').on('change', function() {
            if ($(this).val() === 'Yes') {
                $('#footerField').slideDown();
            } else {
                $('#footerField').slideUp();
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
        
        // Font toggle
        $('input[name="provide_font"]').on('change', function() {
            if ($(this).val() === 'Yes') {
                $('#fontField').slideDown();
                $('#fontField input').attr('required', true);
            } else {
                $('#fontField').slideUp();
                $('#fontField input').attr('required', false);
            }
        });
        
        // Dumpster color toggle
        $('input[name="dumpster_color"]').on('change', function() {
            if ($(this).val() === 'Custom') {
                $('#customDumpsterImages').slideDown();
                $('#customDumpsterImages input').attr('required', true);
            } else {
                $('#customDumpsterImages').slideUp();
                $('#customDumpsterImages input').attr('required', false);
            }
        });
        
        // Dumpster types toggle
        $('.dumpster-type').on('change', function() {
            const type = $(this).val();
            let sectionId = '';
            
            if (type === 'Roll-Off') sectionId = '#rollOffSection';
            else if (type === 'Hook-Lift') sectionId = '#hookLiftSection';
            else if (type === 'Dump Trailers') sectionId = '#dumpTrailerSection';
            
            if ($(this).is(':checked')) {
                $(sectionId).slideDown();
            } else {
                $(sectionId).slideUp();
            }
        });
        
        // Services offered toggle
        $('input[name="services_offered[]"]').on('change', function() {
            const junkRemovalChecked = $('input[name="services_offered[]"][value*="Junk Removal"]').is(':checked');
            
            if (junkRemovalChecked) {
                $('#junkRemovalSection').slideDown();
                $('#junkRemovalSection input[type="checkbox"]').attr('required', true);
            } else {
                $('#junkRemovalSection').slideUp();
                $('#junkRemovalSection input').attr('required', false);
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
            formData.append('action', 'docket_submit_standard_build_form');
            formData.append('nonce', '<?php echo wp_create_nonce("docket_standard_build_nonce"); ?>');
            
            // Submit via AJAX
            $.ajax({
                url: docket_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('.docket-standard-form form').hide();
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
            alert('Full terms would be displayed in a modal or expanded view');
        };
    });
    </script>
    <?php
}

/**
 * AJAX Handler for form submission
 */
add_action('wp_ajax_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');
add_action('wp_ajax_nopriv_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');

function docket_handle_standard_build_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_standard_build_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Prepare email content
    $email_content = "Standard Build Form Submission\n";
    $email_content .= "=============================\n\n";
    
    // Add all form fields
    foreach ($_POST as $key => $value) {
        if ($key !== 'action' && $key !== 'nonce') {
            $label = ucwords(str_replace('_', ' ', $key));
            if (is_array($value)) {
                $email_content .= $label . ": " . implode(', ', $value) . "\n";
            } else {
                $email_content .= $label . ": " . $value . "\n";
            }
        }
    }
    
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
    $subject = 'Standard Build Form Submission - ' . sanitize_text_field($_POST['business_name']);
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    $sent = wp_mail($to, $subject, $email_content, $headers);
    
    if ($sent) {
        wp_send_json_success(array('message' => 'Form submitted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to send email'));
    }
}