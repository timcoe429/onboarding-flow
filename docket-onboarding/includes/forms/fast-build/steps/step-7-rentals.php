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
    
    <!-- Universal Dumpster Entry System -->
    <div id="rollOffSection" class="rental-section" style="display: none;">
        <h4>🗑️ Roll-Off Dumpster Information</h4>
        <div class="dumpster-entries" id="rollOffEntries">
            <!-- Dumpster entries will be added here dynamically -->
        </div>
        <button type="button" class="add-dumpster-btn" data-type="roll-off">+ Add Roll-Off Dumpster</button>
    </div>

    <div id="hookLiftSection" class="rental-section" style="display: none;">
        <h4>🚛 Hook-Lift Dumpster Information</h4>
        <div class="dumpster-entries" id="hookLiftEntries">
            <!-- Dumpster entries will be added here dynamically -->
        </div>
        <button type="button" class="add-dumpster-btn" data-type="hook-lift">+ Add Hook-Lift Dumpster</button>
    </div>

    <div id="dumpTrailerSection" class="rental-section" style="display: none;">
        <h4>🚚 Dump Trailer Information</h4>
        <div class="dumpster-entries" id="dumpTrailerEntries">
            <!-- Dumpster entries will be added here dynamically -->
        </div>
        <button type="button" class="add-dumpster-btn" data-type="dump-trailer">+ Add Dump Trailer</button>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
