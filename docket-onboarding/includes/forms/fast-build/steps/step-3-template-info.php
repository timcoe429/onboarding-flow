<!-- Step 3: Template Information -->
<div class="form-step" data-step="3">
    <h2><?php docket_form_content('fast-build', 3, 'form_title', 'Fast Build Template Information'); ?></h2>
    <p class="step-subtitle"><?php docket_form_content('fast-build', 3, 'form_subtitle', 'Important information about your Fast Build template'); ?></p>
    
    <div class="info-box">
        <h4><?php docket_form_content('fast-build', 3, 'info_title', 'What\'s Included in Fast Build'); ?></h4>
        
        <div class="info-section">
            <h5>Stock Content Only</h5>
            <p><?php docket_form_content('fast-build', 3, 'stock_content', 'Your website will be built with placeholder content and stock images. You\'ll need to customize all text and images after launch.'); ?></p>
        </div>

        <div class="info-section">
            <h5>No Revisions</h5>
            <p><?php docket_form_content('fast-build', 3, 'no_revisions', 'Fast Build includes zero revision rounds. What you see in the template preview is what you\'ll receive.'); ?></p>
        </div>

        <div class="info-section">
            <h5>Self-Customization Required</h5>
            <p><?php docket_form_content('fast-build', 3, 'self_customization', 'You\'ll receive WordPress/Elementor access to customize your site. Make sure you\'re comfortable with these tools.'); ?></p>
        </div>

        <div class="info-section">
            <h5>3-Day Turnaround</h5>
            <p><?php docket_form_content('fast-build', 3, 'turnaround', 'Your website will be ready to launch within 3 business days of payment and domain setup.'); ?></p>
        </div>
    </div>
    
    <label class="checkbox-card">
        <input type="checkbox" name="understand_fast_build" value="understood" required>
        <span><?php docket_form_content('fast-build', 3, 'acceptance_text', 'I understand the Fast Build limitations and am ready to proceed'); ?></span>
    </label>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
