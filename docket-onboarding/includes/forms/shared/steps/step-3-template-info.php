<!-- Step 3: Template Information -->
<div class="form-step" data-step="3">
    <?php 
    // Get form type from context (passed via unified renderer)
    global $docket_current_form_type;
    $form_type = isset($docket_current_form_type) ? $docket_current_form_type : 'standard-build';
    
    // Set default content, checkbox config, and heading based on form type
    $default_content = '';
    $heading = '';
    $subtitle = '';
    $checkbox_name = '';
    $checkbox_value = '';
    $checkbox_label = '';
    
    switch ($form_type) {
        case 'fast-build':
            $heading = 'Fast Build Template Information';
            $subtitle = 'Important information about your Fast Build template';
            $checkbox_name = 'understand_fast_build';
            $checkbox_value = 'understood';
            $checkbox_label = 'I understand the Fast Build limitations and am ready to proceed';
            
            $default_content = '
<h2>Fast Build Template Information</h2>
<p>Important information about your Fast Build template</p>

<div class="info-box">
    <h4>What\'s Included in Fast Build</h4>
    
    <div class="info-section">
        <h5>Stock Content Only</h5>
        <p>Your website will be built with placeholder content and stock images. You\'ll need to customize all text and images after launch.</p>
    </div>

    <div class="info-section">
        <h5>No Revisions</h5>
        <p>Fast Build includes zero revision rounds. What you see in the template preview is what you\'ll receive.</p>
    </div>

    <div class="info-section">
        <h5>Self-Customization Required</h5>
        <p>You\'ll receive WordPress/Elementor access to customize your site. Make sure you\'re comfortable with these tools.</p>
    </div>

    <div class="info-section">
        <h5>3-Day Turnaround</h5>
        <p>Your website will be ready to launch within 3 business days of payment and domain setup.</p>
    </div>
</div>
';
            break;
            
        case 'website-vip':
            $heading = 'Website Template Information';
            $subtitle = 'You will now get to select your website template!';
            $checkbox_name = 'accept_webbuild_terms';
            $checkbox_value = 'I Understand';
            $checkbox_label = 'I understand the above clarifications on the process';
            
            $default_content = '
<h2>Website Template Information</h2>
<p class="step-subtitle">You will now get to select your website template!</p>

<div class="info-box">
    <p>We will customize your website based on the template you choose, and the information you provide.</p>
    
    <div class="info-section">
        <h5>Customized Website Information</h5>
        <p>Please note that this is not a full custom website, it is a pre-built website theme that we add your content and images to. We do not do any additional design work or custom requests that are not already built into the website theme. This includes but isn\'t limited to logo design, product image design, adding new pages, adding new sections, and more.</p>
    </div>

    <div class="info-section">
        <h5>Sections & Pages Included per Template</h5>
        <p>Once you choose a template, you\'ll be able to preview the pages and sections included. Please note: the template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.</p>
    </div>

    <div class="info-section">
        <h5>Revisions to Template</h5>
        <p>Since the website is considered a small website by industry standards, (less than 10 pages) we limit our revision round to 1. You will be notified once your website is ready to be reviewed.</p>
    </div>

    <div class="info-section">
        <h5>Review Period</h5>
        <p>Once your revision round is done (You have 3 full days to fully review the site and add in any changes you see within the scope and theme that you selected) we will make the changes you request that are within the scope we can provide, and then your website will be ready to be pushed live. <strong>Once your website is live, you\'ll be on our WebsiteVIP plan where our team manages edits to your website.</strong></p>
    </div>

    <div class="info-section">
        <h5>Charges for Additional/Out of Scope Customizations</h5>
        <p>If you want our team to provide any customizations outside of scope and not included in the theme you chose, such as adding new pages, or if you would like our team to implement edits that you do not include in your review period, will be charged $175/hr for our team to execute these changes during the website build.</p>
    </div>

    <div class="info-section">
        <p>The amount paid is only refundable if we have not fulfilled our obligations to deliver the work required under the agreement. The total paid is not refundable if the development work has been started and you terminate the contract or work through no fault of ours, or if you accept ownership of the project transferred to you.</p>
    </div>
</div>
';
            break;
            
        default: // standard-build
            $heading = 'Website Template Information';
            $subtitle = 'You will now get to select your website template!';
            $checkbox_name = 'accept_webbuild_terms';
            $checkbox_value = 'I Understand';
            $checkbox_label = 'I understand the above clarifications on the process';
            
            $default_content = '
<h2>Website Template Information</h2>
<p class="step-subtitle">You will now get to select your website template!</p>

<div class="info-box">
    <h4>Important Information About Your Template</h4>
    
    <div class="info-section">
        <h5>Customized Website Information</h5>
        <p>You will have the ability to customize your website beyond the template design once the website is launched and self-managed.</p>
    </div>

    <div class="info-section">
        <h5>Sections & Pages Included</h5>
        <p>The template preview shows exactly what\'s available — we do not add additional pages or sections beyond what is shown.</p>
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
';
            break;
    }
    
    // Get the entire content for this step from database (with default fallback)
    $content = docket_get_form_content($form_type, 3, 'content', $default_content);
    
    // Output the entire content as HTML
    echo wp_kses_post($content);
    ?>
    
    <!-- Checkbox (rendered separately for consistency) -->
    <label class="checkbox-card">
        <input type="checkbox" name="<?php echo esc_attr($checkbox_name); ?>" value="<?php echo esc_attr($checkbox_value); ?>" required>
        <span><?php echo esc_html($checkbox_label); ?></span>
    </label>
    
    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>

