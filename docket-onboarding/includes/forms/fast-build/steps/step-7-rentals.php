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
