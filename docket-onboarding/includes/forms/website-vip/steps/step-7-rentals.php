<!-- Step 7: Rentals Information -->
<div class="form-step" data-step="7">
    <h2>Rentals Information</h2>
    <p class="step-subtitle">Tell us about your rental services</p>
    
    <div class="form-field">
        <label>What color are your dumpsters? *</label>
        <p class="field-note">We provide stock images for: Black, Grey, Green, Red, Orange, and Blue</p>
        
        <div class="radio-group compact">
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Black" required>
                <div class="color-dot" style="background-color: #000000;"></div>
                <span>Black</span>
            </label>
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Blue" required>
                <div class="color-dot" style="background-color: #0066CC;"></div>
                <span>Blue</span>
            </label>
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Grey" required>
                <div class="color-dot" style="background-color: #666666;"></div>
                <span>Grey</span>
            </label>
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Orange" required>
                <div class="color-dot" style="background-color: #FF6600;"></div>
                <span>Orange</span>
            </label>
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Red" required>
                <div class="color-dot" style="background-color: #FF0000;"></div>
                <span>Red</span>
            </label>
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Green" required>
                <div class="color-dot" style="background-color: #00AA00;"></div>
                <span>Green</span>
            </label>
            <label>
                <input type="radio" name="dumpster_stock_image_color_selection" value="Custom" required>
                <span>I'll provide images</span>
            </label>
        </div>
    </div>
    
    <div class="form-field" id="customDumpsterImages" style="display: none;">
            <label>Upload Dumpster Images *</label>
            <div class="file-upload small">
                <input type="file" name="dumpster_images[]" accept="image/*" multiple>
                <div class="file-upload-text">
                    <span>Upload Images</span>
                    <small>600px x 400px preferred</small>
                </div>
            </div>
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
        <h4>Dumpster Rental Information for Your Website</h4>
        <ul>
            <li><strong>Rental Sizes:</strong> e.g., 10 yd, 20 yd, 30 yd</li>
            <li><strong>Rental Periods:</strong> e.g., 1, 3, and 7 Day Rentals</li>
            <li><strong>Tons Allowed:</strong> We'll show lowest tonnage with '+' (e.g., 2+ Tons)</li>
            <li><strong>Pricing:</strong> We'll show starting price with '+' (e.g., $399+)</li>
        </ul>
    </div>

    <!-- Dynamic Rental Info Sections -->
    <div id="rollOffSection" class="rental-section" style="display: none;">
        <h4>🗑️ Roll-Off Dumpster Information</h4>
        <div class="form-grid-2col">
            <div class="form-field">
                <label>Rental Sizes</label>
                <textarea name="roll_sizes" rows="2" placeholder="e.g., 10 yd, 20 yd, 30 yd"></textarea>
            </div>
            <div class="form-field">
                <label>Rental Periods</label>
                <textarea name="roll_rentalperiods" rows="2" placeholder="e.g., 1, 3, and 7 Day Rentals"></textarea>
            </div>
            <div class="form-field">
                <label>Tons Allowed</label>
                <textarea name="roll_tons" rows="2" placeholder="e.g., 2 tons, 3 tons"></textarea>
            </div>
            <div class="form-field">
                <label>Starting Prices</label>
                <textarea name="roll_startingprice" rows="2" placeholder="e.g., $299"></textarea>
            </div>
        </div>
    </div>

    <div id="hookLiftSection" class="rental-section" style="display: none;">
        <h4>🚛 Hook-Lift Dumpster Information</h4>
        <div class="form-grid-2col">
            <div class="form-field">
                <label>Rental Sizes</label>
                <textarea name="hook_rentalsizes" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Rental Periods</label>
                <textarea name="hook_rentalperiods" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Tons Allowed</label>
                <textarea name="hook_rentaltons" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Starting Prices</label>
                <textarea name="hook_price" rows="2"></textarea>
            </div>
        </div>
    </div>

    <div id="dumpTrailerSection" class="rental-section" style="display: none;">
        <h4>🚚 Dump Trailer Information</h4>
        <div class="form-grid-2col">
            <div class="form-field">
                <label>Rental Sizes</label>
                <textarea name="dump_trailersize" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Rental Periods</label>
                <textarea name="dump_trailerrentals" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Tons Allowed</label>
                <textarea name="dump_trailertons" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Starting Prices</label>
                <textarea name="dump_trailerprice" rows="2"></textarea>
            </div>
        </div>
    </div>

    <!-- Junk Removal Section -->
    <div id="junkRemovalSection" style="display: none;">
        <div class="info-box">
            <h4>Junk Removal Information</h4>
            <ul>
                <li><strong>No Pricing:</strong> Customers will click "Get Estimate" for quotes</li>
                <li><strong>Types:</strong> Select residential, commercial, or both</li>
            </ul>
        </div>
        
        <div class="form-field">
            <label>What junk removal services do you offer? *</label>
            <div class="checkbox-group">
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Hoarding Cleanouts">
                    <span>Residential - Hoarding Cleanouts</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Bagsters/Junk Bags">
                    <span>Residential - Bagsters/Junk Bags</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - By the Truckload">
                    <span>Residential - By the Truckload</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Single-Item Disposal">
                    <span>Residential - Single-Item Disposal</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Construction Debris Removal">
                    <span>Commercial - Construction Debris</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Bagsters/Junk Bags">
                    <span>Commercial - Bagsters/Junk Bags</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - By the Truckload">
                    <span>Commercial - By the Truckload</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Single-Item Disposal">
                    <span>Commercial - Single-Item Disposal</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
