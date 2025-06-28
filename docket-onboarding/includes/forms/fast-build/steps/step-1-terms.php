<!-- Step 1: Terms & WordPress Knowledge -->
<div class="form-step active" data-step="1">
    <h2>Fast Build Website</h2>
    <p class="step-subtitle">Let's start by reviewing the terms and checking your WordPress experience</p>
    
    <!-- Terms Section -->
    <div class="terms-box mb-20">
        <div class="terms-content">
            <h4>Fast Build Terms & Conditions</h4>
            <p>By proceeding with the Fast Build option, you understand:</p>
            <ul>
                <li>Your website will be ready in 3 days</li>
                <li>Zero revisions before launch - customization is your responsibility</li>
                <li>Stock content and images will be used</li>
                <li>You'll need WordPress/Elementor knowledge to customize</li>
                <li>Payment is due immediately to begin work</li>
                <li>Changes after launch are charged at $175/hour</li>
            </ul>
            
            <p class="terms-link">
                <a href="#" onclick="showFullTerms(); return false;">View Full Terms</a>
            </p>
        </div>
        
        <label class="checkbox-card">
            <input type="checkbox" name="accept_terms" value="accepted" required>
            <span>I accept the terms & conditions</span>
        </label>
    </div>
    
    <!-- WordPress Experience -->
    <div class="form-field">
        <label>WordPress/Elementor Experience *</label>
        <p class="field-note">How would you rate your experience?</p>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="wordpress_exp" value="Beginner" required>
                <div class="radio-card-content">
                    <strong>Beginner</strong>
                    <span>I've never used WordPress/Elementor or only a few times</span>
                </div>
            </label>
            
            <label class="radio-card">
                <input type="radio" name="wordpress_exp" value="Intermediate" required>
                <div class="radio-card-content">
                    <strong>Intermediate</strong>
                    <span>I've used it enough to feel comfortable making edits</span>
                </div>
            </label>
            
            <label class="radio-card">
                <input type="radio" name="wordpress_exp" value="Expert" required>
                <div class="radio-card-content">
                    <strong>Expert</strong>
                    <span>I use it regularly and am very confident</span>
                </div>
            </label>
        </div>
    </div>

    <div class="notice-box" id="wpNotice" style="display: none;">
        <p class="notice-text"></p>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
