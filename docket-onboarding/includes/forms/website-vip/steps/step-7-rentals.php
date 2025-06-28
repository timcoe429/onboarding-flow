<!-- Step 7: Rentals Information -->
<div class="form-step" data-step="7">
    <h2>Rentals Information</h2>
    <p class="step-subtitle">Tell us about your rental services</p>
    
    <div class="form-grid-3col">
        <div class="form-field">
            <label>14. What color are your dumpsters? *</label>
            <p class="field-note">We can provide stock dumpster images for the following colors: Black, Grey, Green, Red, Orange, and Blue. If we don't offer your dumpster color for stock images, you can either provide images you'd like us to use OR we will use black dumpsters as default.</p>
        </div>
        
        <div class="form-field">
            <div class="radio-group compact">
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="Black" required><span>Black</span></label>
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="Blue" required><span>Blue</span></label>
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="Grey" required><span>Grey</span></label>
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="Orange" required><span>Orange</span></label>
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="Red" required><span>Red</span></label>
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="Green" required><span>Green</span></label>
                <label><input type="radio" name="dumpster_stock_image_color_selection" value="I'll provide my own images" required><span>I'll provide my own images</span></label>
            </div>
        </div>
        
        <div class="form-field" id="customDumpsterImages" style="display: none;">
            <label>I'd like to provide images instead *</label>
            <div class="file-upload small">
                <input type="file" name="id_like_to_provide_images_instead[]" accept="image/*" multiple id="dumpsterImageInput">
                <div class="file-upload-text">
                    <span>Upload Images</span>
                    <small>Preferred Size: 600px x 400px or similar specs.</small>
                </div>
            </div>
            <div class="file-list" id="dumpsterFileList"></div>
        </div>
    </div>
    
    <div class="form-field">
        <label>15. What services do you offer? *</label>
        <div class="checkbox-group">
            <label class="checkbox-card">
                <input type="checkbox" name="what_services_do_you_offer[]" value="Just Dumpster Rentals">
                <span>Just Dumpster Rentals</span>
            </label>
            <label class="checkbox-card">
                <input type="checkbox" name="what_services_do_you_offer[]" value="Dumpster Rentals & Junk Removal">
                <span>Dumpster Rentals & Junk Removal</span>
            </label>
        </div>
    </div>
    
    <div class="form-field">
        <label>16. What types of dumpsters do you have?</label>
        <div class="checkbox-group">
            <label class="checkbox-card">
                <input type="checkbox" name="what_types_of_dumpsters_do_you_have[]" value="Roll-Off" class="dumpster-type">
                <span>Roll-Off</span>
            </label>
            <label class="checkbox-card">
                <input type="checkbox" name="what_types_of_dumpsters_do_you_have[]" value="Hook-Lift" class="dumpster-type">
                <span>Hook-Lift</span>
            </label>
            <label class="checkbox-card">
                <input type="checkbox" name="what_types_of_dumpsters_do_you_have[]" value="Dump Trailers" class="dumpster-type">
                <span>Dump Trailers</span>
            </label>
        </div>
    </div>
    
    <div class="info-box">
        <h4>Dumpster Rentals Information Displayed on Your Website</h4>
        <ul>
            <li><strong>Rental Sizes:</strong> This is the size of your rentals. <em>Example: 10 yd, 20 yd, 30 yd</em></li>
            <li><strong>Rental Periods:</strong> This is the lengths of time you offer your rentals. <em>Example: 1, 3, and 7 Day Rentals</em></li>
            <li><strong>Tons Allowed:</strong> We will only add the lowest tonnage allowed for your rentals to your website. If you offer varying tonnage, we will add the lowest tonnage followed by a '+' sign to note that tonnage changes based on rental. <em>Example: 2+ Tons Allowed</em></li>
            <li><strong>Pricing:</strong> We will only add your starting pricing for your rentals (the lowest price you offer) to your website, followed by a '+' sign to note that pricing increases based on rental. <em>Example: $399+</em></li>
        </ul>
    </div>

    <!-- Dynamic Rental Info Sections -->
    <div id="rollOffSection" class="rental-section" style="display: none;">
        <h4><i>🗑️</i> Roll-Off Dumpster Rental Information</h4>
        <div class="form-grid-2col">
            <div class="form-field">
                <label>Roll-Off Dumpster Rental Sizes</label>
                <textarea name="roll_sizes" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Roll-Off Dumpster Rental Periods</label>
                <textarea name="roll_rentalperiods" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Roll-Off Dumpster Rental Tons Allowed</label>
                <textarea name="roll_tons" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Roll-Off Dumpster Rental Starting Prices</label>
                <textarea name="roll_startingprice" rows="2"></textarea>
            </div>
        </div>
    </div>

    <div id="hookLiftSection" class="rental-section" style="display: none;">
        <h4><i>🚛</i> Hook-Lift Dumpster Rental Information</h4>
        <div class="form-grid-2col">
            <div class="form-field">
                <label>Hook-Lift Dumpster Rental Sizes</label>
                <textarea name="hook_rentalsizes" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Hook-Lift Dumpster Rental Periods</label>
                <textarea name="hook_rentalperiods" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Hook-Lift Dumpster Rental Tons Allowed</label>
                <textarea name="hook_rentaltons" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Hook-Lift Dumpster Rental Starting Prices</label>
                <textarea name="hook_price" rows="2"></textarea>
            </div>
        </div>
    </div>

    <div id="dumpTrailerSection" class="rental-section" style="display: none;">
        <h4><i>🚚</i> Dump Trailer Rental Information</h4>
        <div class="form-grid-2col">
            <div class="form-field">
                <label>Dump Trailer Rental Sizes</label>
                <textarea name="dump_trailersize" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Dump Trailer Rental Periods</label>
                <textarea name="dump_trailerrentals" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Dump Trailer Rental Tons Allowed</label>
                <textarea name="dump_trailertons" rows="2"></textarea>
            </div>
            <div class="form-field">
                <label>Dump Trailer Rental Starting Prices</label>
                <textarea name="dump_trailerprice" rows="2"></textarea>
            </div>
        </div>
    </div>

    <!-- Junk Removal Section -->
    <div id="junkRemovalSection" style="display: none;">
        <div class="info-box">
            <h4>Junk Removal Information Displayed on Your Website</h4>
            <ul>
                <li><strong>No Pricing:</strong> We won't include Junk Removal pricing on your website. Instead, customers will be directed to click a button, "Get Estimate", allowing them to provide more information about their job request.</li>
                <li><strong>Junk Removal Types:</strong> You can note if you do residential, commercial, or both types of junk removal.</li>
                <li><strong>Additional Information:</strong> If there is other information you'd like to add to the Junk Removal product(s) on your website, you can request to do so during the website review.</li>
            </ul>
        </div>
        
        <div class="form-field">
            <label>What information do you want to include about your junk removal services? *</label>
            <div class="checkbox-group">
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Hoarding Cleanouts">
                    <span>Residential Junk Removal - Hoarding Cleanouts</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Bagsters/Junk Bags">
                    <span>Residential Junk Removal - Bagsters/Junk Bags</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - By the Truckload">
                    <span>Residential Junk Removal - By the Truckload</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Residential Junk Removal - Single-Item Disposal">
                    <span>Residential Junk Removal - Single-Item Disposal</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Construction Debris Removal">
                    <span>Commercial Junk Removal - Construction Debris Removal</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Bagsters/Junk Bags">
                    <span>Commercial Junk Removal - Bagsters/Junk Bags</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - By the Truckload">
                    <span>Commercial Junk Removal - By the Truckload</span>
                </label>
                <label class="checkbox-card">
                    <input type="checkbox" name="junk_removal[]" value="Commercial Junk Removal - Single-Item Disposal">
                    <span>Commercial Junk Removal - Single-Item Disposal</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
