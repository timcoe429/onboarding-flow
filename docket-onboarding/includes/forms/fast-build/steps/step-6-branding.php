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
