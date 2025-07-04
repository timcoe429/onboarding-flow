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
        <label>Logo File(s) *</label>
        <div class="file-upload">
            <input type="file" name="logo_files[]" accept="image/*" multiple id="logoFileInput">
            <div class="file-upload-text">
📁
                <span>Click to upload or drag files here</span>
                <small>Preferred size: 300px x 300px or similar dimensions</small>
            </div>
        </div>
        <div class="file-list" id="logoFileList"></div>
    </div>
    
    <div class="info-box mb-20">
        <p><strong>Company Colors</strong></p>
        <p>We need the exact HEX code of the colors you'd like us to use. You can use <a href="https://htmlcolorcodes.com/color-picker/" target="_blank" style="color: #185fb0; font-weight: bold;">this color picker tool</a> to find exact HEX codes.</p>
    </div>
    
    <div class="form-grid-2col">
        <div class="form-field">
            <label>Company Color 1 HEX Code *</label>
            <div class="color-input-wrapper">
                <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" required class="hex-input">
                <input type="color" class="color-picker" value="#00008B">
            </div>
            <small>Click the color box or enter HEX code</small>
        </div>
        
        <div class="form-field">
            <label>Company Color 2 HEX Code</label>
            <div class="color-input-wrapper">
                <input type="text" name="company_colors2" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" class="hex-input">
                <input type="color" class="color-picker" value="#00008B">
            </div>
            <small>Optional second color</small>
        </div>
    </div>
    
    <div class="info-box mb-20">
        <p><strong>Note for Fast Build</strong></p>
        <p>Your website will use your template's default fonts. You can customize fonts after launch using the Elementor page builder.</p>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
