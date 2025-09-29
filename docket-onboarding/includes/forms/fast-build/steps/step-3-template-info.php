<!-- Step 3: Template Information -->
<div class="form-step" data-step="3">
    <?php 
    // Get the entire content for this step
    $content = docket_get_form_content('fast-build', 3, 'content', '
<h2>Fast Build Template Information</h2>
<p>Important information about your Fast Build template</p>

<div class="info-box">
    <h4>What\'s Included in Fast Build</h4>
    
    <div class="info-section">
        <h5>Stock Content Only</h5>
        <p>Your website will be built with placeholder content and stock images. You\'ll need to customize all text and images after launch.</p>
    </div>

    <div class="info-section">
        <h5>No Revisions</h5>
        <p>Fast Build includes zero revision rounds. What you see in the template preview is what you\'ll receive.</p>
    </div>

    <div class="info-section">
        <h5>Self-Customization Required</h5>
        <p>You\'ll receive WordPress/Elementor access to customize your site. Make sure you\'re comfortable with these tools.</p>
    </div>

    <div class="info-section">
        <h5>3-Day Turnaround</h5>
        <p>Your website will be ready to launch within 3 business days of payment and domain setup.</p>
    </div>
</div>

<label class="checkbox-card">
    <input type="checkbox" name="understand_fast_build" value="understood" required>
    <span>I understand the Fast Build limitations and am ready to proceed</span>
</label>

');
    
    // Output the entire content as HTML
    echo wp_kses_post($content);
    ?>
    
    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
