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

            <!-- Step 1: WordPress Knowledge -->
            <div class="form-step active" data-step="1">
                <h2>WordPress/Elementor Experience</h2>
                <p class="step-subtitle">How would you rate your experience?</p>
                
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

                <div class="notice-box" id="wpNotice" style="display: none;">
                    <p class="notice-text"></p>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 2: Terms -->
            <div class="form-step" data-step="2">
                <h2>Terms & Conditions</h2>
                <p class="step-subtitle">Please review and accept to continue</p>
                
                <div class="terms-box">
                    <div class="terms-content">
                        <h4>Website Design & Development Agreement</h4>
                        <p>By accepting, you agree to Docket's standard terms for website design and development.</p>
                        
                        <h5>Key Points:</h5>
                        <ul>
                            <li>Payment is due immediately to begin work</li>
                            <li>You must provide all materials before project start</li>
                            <li>Changes after launch are $175/hour</li>
                            <li>Website ownership remains with Docket</li>
                            <li>Backend access is limited to Docket only</li>
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

                <div class="form-nav">
                    <button type="button" class="btn-prev">Back</button>
                    <button type="button" class="btn-next">Next</button>
                </div>
            </div>

            <!-- Step 3: Contact Info -->
            <div class="form-step" data-step="3">
                <h2>Contact & Business Info</h2>
                <p class="step-subtitle">This will be displayed on your website</p>
                
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
                    
                    <div class="form-field full-width">
                        <label>Business Email *</label>
                        <input type="email" name="business_email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-field full-width">
                        <label>Business Address *</label>
                        <input type="text" name="business_address" autocomplete="street-address"  required>
                    </div>
                </div>

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

            <!-- Step 5: Service Areas -->
            <div class="form-step" data-step="5">
                <h2>Service Areas</h2>
                <p class="step-subtitle">List up to 9 areas you service (cities, counties, regions)</p>
                
                <?php if ($plan_type === 'pro'): ?>
                <div class="form-field" style="margin-bottom: 30px;">
                    <label>Blog Content Focus *</label>
                    <div class="radio-group compact">
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
                    <div class="form-field">
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
                
                <div class="form-grid-2col">
                    <div class="form-field">
                        <label>Primary Color (HEX) *</label>
                        <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="#00008B" required>
                        <small>Use <a href="https://imagecolorpicker.com" target="_blank">this tool</a> to find HEX codes</small>
                    </div>
                    
                    <div class="form-field">
                        <label>Secondary Color (HEX)</label>
                        <input type="text" name="company_colors2" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="#FF6B6B">
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
                <p class="step-subtitle">Tell us about your services</p>
                
                <div class="form-field">
                    <label>Dumpster Colors *</label>
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
                        <label class="color-option">
                            <input type="radio" name="dumpster_color" value="Custom" required>
                            <span class="color-box" style="background: linear-gradient(45deg, #ddd 25%, transparent 25%)"></span>
                            <span>Custom</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>Services Offered *</label>
                    <label class="checkbox-card">
                        <input type="checkbox" name="services_offered[]" value="Dumpster Rentals" checked>
                        <span>Dumpster Rentals</span>
                    </label>
                    <label class="checkbox-card">
                        <input type="checkbox" name="services_offered[]" value="Junk Removal">
                        <span>Junk Removal</span>
                    </label>
                </div>
                
                <div class="form-field">
                    <label>Dumpster Types</label>
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

                <!-- Dynamic sections for each type -->
                <div id="rollOffInfo" class="rental-info" style="display: none;">
                    <h4>Roll-Off Details</h4>
                    <input type="text" name="roll_sizes" placeholder="Sizes (e.g., 10yd, 20yd, 30yd)">
                    <input type="text" name="roll_pricing" placeholder="Starting price (e.g., $299+)">
                </div>

                <div id="hookLiftInfo" class="rental-info" style="display: none;">
                    <h4>Hook-Lift Details</h4>
                    <input type="text" name="hook_sizes" placeholder="Sizes">
                    <input type="text" name="hook_pricing" placeholder="Starting price">
                </div>

                <div id="dumpTrailerInfo" class="rental-info" style="display: none;">
                    <h4>Dump Trailer Details</h4>
                    <input type="text" name="trailer_sizes" placeholder="Sizes">
                    <input type="text" name="trailer_pricing" placeholder="Starting price">
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
                    <button type="submit" class="btn-submit">Complete Order</button>
                </div>
            </div>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2>Order Submitted!</h2>
            <p>Thank you! We'll start building your website right away.</p>
            <p class="success-note">You'll receive a confirmation email shortly.</p>
        </div>
    </div>

    <style>
    /* Clean, Modern Form Styles */
    .docket-fast-form {
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
    .form-field input[type="tel"] {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .form-field input:focus {
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

    /* Grid Layouts */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .form-grid-2col {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 20px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .service-areas-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
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

    /* Compact radio group */
    .radio-group.compact .radio-card {
        margin-bottom: 8px;
    }

    .radio-group.compact .radio-card-content {
        padding: 12px 16px;
        font-size: 14px;
    }

    /* Inline Radio */
    .radio-inline {
        display: flex;
        gap: 20px;
    }

    .radio-inline label {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .radio-inline input {
        margin-right: 6px;
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

    /* Template Grid */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .template-card {
        cursor: pointer;
    }

    .template-card input {
        position: absolute;
        opacity: 0;
    }

    .template-preview {
        display: block;
        border: 3px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .template-preview img {
        width: 100%;
        height: 140px;
        object-fit: cover;
    }

    .template-name {
        display: block;
        padding: 10px;
        text-align: center;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .template-card:hover .template-preview {
        border-color: #d1d5db;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .template-card input:checked + .template-preview {
        border-color: #185fb0;
    }

    /* Color Grid */
    .color-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        gap: 10px;
    }

    .color-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        padding: 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .color-option:hover {
        background: #f3f4f6;
    }

    .color-option input {
        position: absolute;
        opacity: 0;
    }

    .color-box {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        margin-bottom: 6px;
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }

    .color-option input:checked ~ .color-box {
        border-color: #111827;
        transform: scale(1.1);
    }

    .color-option span {
        font-size: 12px;
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
        max-height: 250px;
        overflow-y: auto;
        margin-bottom: 16px;
    }

    .terms-content h4 {
        margin-bottom: 10px;
        color: #111827;
        font-size: 16px;
    }

    .terms-content h5 {
        margin-top: 16px;
        margin-bottom: 10px;
        color: #374151;
        font-size: 14px;
    }

    .terms-content ul {
        margin-left: 20px;
        color: #6b7280;
        font-size: 14px;
    }

    .terms-content li {
        margin-bottom: 6px;
    }

    .terms-link {
        text-align: center;
        margin-top: 12px;
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

    /* Rental Info Sections */
    .rental-info {
        background: #f9fafb;
        border-radius: 6px;
        padding: 16px;
        margin-top: 16px;
    }

    .rental-info h4 {
        margin-bottom: 12px;
        color: #374151;
        font-size: 15px;
    }

    .rental-info input {
        width: 100%;
        margin-bottom: 10px;
    }

    /* Notice Box */
    .notice-box {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 6px;
        padding: 14px;
        margin-top: 16px;
    }

    .notice-box p {
        margin: 0;
        color: #92400e;
        font-size: 13px;
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
        .docket-fast-form {
            padding: 16px;
        }

        .form-step h2 {
            font-size: 22px;
        }

        .form-grid,
        .form-grid-2col {
            grid-template-columns: 1fr;
        }

        .template-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .service-areas-grid {
            grid-template-columns: 1fr;
        }

        .docket-progress-dots {
            padding: 0;
        }

        .docket-progress-dots span {
            width: 24px;
            height: 24px;
            font-size: 11px;
        }

        .radio-card-content {
            padding: 14px;
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
                    if (!$(this).is(':checked')) {
                        valid = false;
                        $(this).closest('.checkbox-group, .checkbox-card, .form-field').addClass('error');
                    }
                } else {
                    // Text inputs - check value more carefully
                    const val = $(this).val();
                    if (!val || val.trim().length === 0) {
                        valid = false;
                        $(this).addClass('error');
                    }
                }
            });
            
            if (!valid) {
                alert('Please fill in all required fields');
                // Focus on first error field
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
                notice.find('.notice-text').html('<strong>Note:</strong> Fast Build requires self-customization. Consider our Standard Build for a fully managed solution.');
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
        
        // Dumpster types toggle
        $('.dumpster-type').on('change', function() {
            const type = $(this).val().replace(/\s+/g, '').toLowerCase();
            const section = $(`#${type}Info`);
            
            if ($(this).is(':checked')) {
                section.slideDown();
            } else {
                section.slideUp();
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
    $email_content = "Fast Build Form Submission\n";
    $email_content .= "==========================\n\n";
    
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
        // Handle file upload logic here
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