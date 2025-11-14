<!-- Step 6: Company Branding -->
<div class="form-step" data-step="6">
    <?php 
    // Get form type from context (passed via unified renderer)
    global $docket_current_form_type;
    $form_type = isset($docket_current_form_type) ? $docket_current_form_type : 'standard-build';
    
    // Determine form-specific features
    $show_match_logo_color = ($form_type !== 'website-vip'); // Fast Build and Standard Build show this
    $company_color_always_visible = ($form_type === 'website-vip'); // Website VIP always shows color field
    $show_font_selection = ($form_type !== 'fast-build'); // Standard Build and Website VIP show font selection
    ?>
    
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
            <input type="file" name="logo_files[]" accept="image/*" multiple id="logoFileInput">
            <div class="file-upload-text">
                <?php if ($form_type === 'website-vip'): ?>
                    <i>📁</i>
                <?php else: ?>
                    📁
                <?php endif; ?>
                <span>Click to upload or drag files here</span>
                <small>Preferred size: 300px x 300px or similar dimensions</small>
            </div>
        </div>
        <div class="file-list" id="logoFileList"></div>
    </div>
    
    <?php if ($show_match_logo_color): ?>
    <div class="form-field">
        <label>Match Primary Logo Color *</label>
        <div class="radio-inline">
            <label>
                <input type="radio" name="match_logo_color" value="Yes" required>
                <span>Yes - Use my logo's primary color</span>
            </label>
            <label>
                <input type="radio" name="match_logo_color" value="No" required>
                <span>No - Choose a different color</span>
            </label>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="form-field" id="companyColorField" style="<?php echo $company_color_always_visible ? '' : 'display: none;'; ?>">
        <label>Primary Company Color *</label>
        <p class="field-note">Used throughout your website. Provide a custom HEX color code</p>
        
        <!-- Custom Color Picker -->
        <div class="custom-color-section">
            <div class="color-input-wrapper">
                <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" class="hex-input">
                <input type="color" class="color-picker" value="#00008B">
            </div>
            <small>Click the color box or enter HEX code</small>
        </div>
    </div>
    
    <div class="info-box mb-20">
        <?php if ($form_type === 'fast-build'): ?>
            <p><strong>Note for Fast Build</strong></p>
            <p>Your website will use your template's default fonts. You can customize fonts after launch using the Elementor page builder.</p>
        <?php else: ?>
            <p><strong>Google Fonts</strong></p>
            <p>We can use a Google Font for your Titles. View available fonts <a href="https://fonts.google.com" target="_blank" style="color: #185fb0; font-weight: bold;">here</a>.</p>
        <?php endif; ?>
    </div>
    
    <?php if ($show_font_selection): ?>
    <div class="form-field">
        <label>Do you want to provide a font for Titles? *</label>
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
    <?php endif; ?>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>

