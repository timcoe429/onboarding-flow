<!-- Step 7: Rentals Info -->
<div class="form-step" data-step="7">
    <h2>Rental Information</h2>
    <p class="step-subtitle">Tell us about your services (we'll use stock images and content)</p>
    
    <div class="form-field">
        <label>What color are your dumpsters? *</label>
        <p class="field-note">We provide stock images for: Black, Grey, Green, Red, Orange, and Blue</p>
        
        <div class="radio-group compact two-columns">
            <label>
                <input type="radio" name="dumpster_color" value="Black" required>
                <div class="color-dot" style="background-color: #000000;"></div>
                <span>Black</span>
            </label>
            <label>
                <input type="radio" name="dumpster_color" value="Blue" required>
                <div class="color-dot" style="background-color: #0066CC;"></div>
                <span>Blue</span>
            </label>
            <label>
                <input type="radio" name="dumpster_color" value="Grey" required>
                <div class="color-dot" style="background-color: #666666;"></div>
                <span>Grey</span>
            </label>
            <label>
                <input type="radio" name="dumpster_color" value="Orange" required>
                <div class="color-dot" style="background-color: #FF6600;"></div>
                <span>Orange</span>
            </label>
            <label>
                <input type="radio" name="dumpster_color" value="Red" required>
                <div class="color-dot" style="background-color: #FF0000;"></div>
                <span>Red</span>
            </label>
            <label>
                <input type="radio" name="dumpster_color" value="Green" required>
                <div class="color-dot" style="background-color: #00AA00;"></div>
                <span>Green</span>
            </label>
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
        <p><strong>Note:</strong> We'll launch your website without showing pricing or tonnage allowed. You'll need to update these after launch.</p>
    </div>
    
    <div class="form-field" id="dumpsterSizesField" style="display: none;">
        <label>Dumpster Sizes & Details</label>
        <p class="field-note">Please list the sizes, dimensions, and any other details for your selected dumpster types</p>
        <textarea name="dumpster_sizes_details" rows="4" placeholder="e.g., Roll-Off: 10 yd (8'x20'), 20 yd (8'x22'), 30 yd (8'x20')&#10;Hook-Lift: 15 yd (6'x12'), 20 yd (6'x16')&#10;Dump Trailers: 12 yd (6'x10'), 16 yd (6'x12')"></textarea>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
