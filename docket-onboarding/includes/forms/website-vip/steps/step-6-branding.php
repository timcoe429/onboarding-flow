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
            <input type="file" name="logo_files[]" accept="image/*" multiple id="logoFileInput">
            <div class="file-upload-text">
                <i>📁</i>
                <span>Click to upload or drag files here</span>
                <small>Preferred size: 300px x 300px or similar dimensions</small>
            </div>
        </div>
        <div class="file-list" id="logoFileList"></div>
    </div>
    
    <div class="form-field">
        <label>Company Color 1 *</label>
        <p class="field-note">Choose a preset color or use the custom picker below</p>
        
        <!-- Preset Colors -->
        <div class="preset-colors">
            <button type="button" class="color-preset" data-color="#FF0000" data-name="Red" style="background-color: #FF0000;">
                <span>Red</span>
            </button>
            <button type="button" class="color-preset" data-color="#0066CC" data-name="Blue" style="background-color: #0066CC;">
                <span>Blue</span>
            </button>
            <button type="button" class="color-preset" data-color="#00AA00" data-name="Green" style="background-color: #00AA00;">
                <span>Green</span>
            </button>
            <button type="button" class="color-preset" data-color="#FF6600" data-name="Orange" style="background-color: #FF6600;">
                <span>Orange</span>
            </button>
            <button type="button" class="color-preset" data-color="#800080" data-name="Purple" style="background-color: #800080;">
                <span>Purple</span>
            </button>
            <button type="button" class="color-preset" data-color="#000000" data-name="Black" style="background-color: #000000;">
                <span>Black</span>
            </button>
            <button type="button" class="color-preset" data-color="#666666" data-name="Gray" style="background-color: #666666;">
                <span>Gray</span>
            </button>
            <button type="button" class="color-preset" data-color="#8B4513" data-name="Brown" style="background-color: #8B4513;">
                <span>Brown</span>
            </button>
        </div>
        
        <!-- Custom Color Picker -->
        <div class="custom-color-section">
            <p class="custom-color-label">Or choose a custom color:</p>
            <div class="color-input-wrapper">
                <input type="text" name="company_colors" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" placeholder="Example: #00008B" required class="hex-input">
                <input type="color" class="color-picker" value="#00008B">
            </div>
            <small>Click the color box or enter HEX code</small>
        </div>
    </div>
    
    <div class="info-box mb-20">
        <p><strong>Google Fonts</strong></p>
        <p>We can use a Google Font for your Headings (H1 and H2). View available fonts <a href="https://fonts.google.com" target="_blank" style="color: #185fb0; font-weight: bold;">here</a>.</p>
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
