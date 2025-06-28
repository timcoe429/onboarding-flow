<!-- Step 7: Rentals Info -->
<div class="form-step" data-step="7">
    <h2>Rental Information</h2>
    <p class="step-subtitle">Tell us about your services (we'll use stock images and content)</p>
    
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
            </div>
        </div>
        
        <div class="form-field">
            <!-- Empty column for spacing -->
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
        <p><strong>Note:</strong> Fast Build uses stock pricing and sizes. You'll need to update these after launch.</p>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
