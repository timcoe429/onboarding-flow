<!-- Step 6: Company Branding -->
<div class="form-step" data-step="6">
    <h2>Company Branding</h2>
    <p class="step-subtitle">Help us match your brand identity</p>
    
    <div class="form-field">
        <label>11. Do you have a logo you'd like to use? *</label>
        <div class="radio-inline">
            <label>
                <input type="radio" name="logo_question" value="Yes — I will provide it" required>
                <span>Yes — I will provide it</span>
            </label>
            <label>
                <input type="radio" name="logo_question" value="No — Use the name of my company instead" required>
                <span>No — Use the name of my company instead</span>
            </label>
        </div>
    </div>
    
    <div class="form-field" id="logoUpload" style="display: none;">
        <label>Logo File(s) *</label>
        <div class="file-upload">
            <input type="file" name="logo_files[]" accept="image/*" multiple id="logoFileInput">
            <div class="file-upload-text">
                <i>📁</i>
                <span>Click to upload or drag files here</span>
                <small>Preferred size: 300px x 300px or similar dimension specs.</small>
            </div>
        </div>
        <div class="file-list" id="logoFileList"></div>
    </div>
    
    <div class="info-box mb-20">
        <p><strong>Company Colors</strong></p>
        <p>We need the exact HEX code of the colors you'd like us to use. You can use a tool like <a href="https://imagecolorpicker.com" target="_blank" style="color: #185fb0; font-weight: bold;">this one</a> to find exact HEX codes.</p>
    </div>
    
    <div class="form-grid-2col">
        <div class="form-field">
            <label>12. Company Color 1 HEX Code *</label>
            <div class="color-picker-wrapper">
                <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" required class="hex-input">
                <input type="color" class="color-picker" value="#00008B">
            </div>
            <small>Please provide specific HEX codes of up to 2 colors.</small>
        </div>
        
        <div class="form-field">
            <label>Company Color 2 HEX Code</label>
            <div class="color-picker-wrapper">
                <input type="text" name="company_colors2" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" class="hex-input">
                <input type="color" class="color-picker" value="#00008B">
            </div>
            <small>Please provide specific HEX codes of up to 2 colors.</small>
        </div>
    </div>
    
    <div class="info-box mb-20">
        <p><strong>We give you the option of providing our team with a Google Font to use on your website.</strong></p>
        <p><em>We can use this font for your Headings (H1 and H2) on your website. Our websites only support Google fonts. You can view a list of Google fonts <strong><a href="https://fonts.google.com" target="_blank">here</a></strong>.</em></p>
    </div>
    
    <div class="form-field">
        <label>13. Do you want to provide a font for your H1 + H2 areas on your website? *</label>
        <div class="radio-inline">
            <label>
                <input type="radio" name="do_you_want_to_provide_a_font_for_your_h1_+_h2_areas_on_your_website" value="Yes — I will provide a Google Font Name" required>
                <span>Yes — I will provide a Google Font Name</span>
            </label>
            <label>
                <input type="radio" name="do_you_want_to_provide_a_font_for_your_h1_+_h2_areas_on_your_website" value="No — Please use the default font for my template" required>
                <span>No — Please use the default font for my template</span>
            </label>
        </div>
    </div>
    
    <div class="form-field" id="fontField" style="display: none;">
        <label>Font Name *</label>
        <input type="text" name="font_name">
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
