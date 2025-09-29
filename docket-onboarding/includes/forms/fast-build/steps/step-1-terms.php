<!-- Step 1: Terms & WordPress Knowledge -->
<div class="form-step active" data-step="1">
    <?php 
    // Get the entire content for this step - THIS PULLS FROM DATABASE
    $content = docket_get_form_content('fast-build', 1, 'content', '
<h2>Fast Build Website</h2>
<p>Let\'s start by reviewing the terms and checking your WordPress experience</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting</h5>
            <p>A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, delivered in just 3 business days with zero revisions before launch.</p>
        </div>
        
        <div class="terms-section">
            <h5>Timeline</h5>
            <p>Your website will be ready in 3 days. Stock content and images will be used to meet this expedited timeline.</p>
        </div>
        
        <div class="terms-section">
            <h5>What We Need From You</h5>
            <ul>
                <li>WordPress/Elementor experience level</li>
                <li>Basic business information and branding</li>
                <li>Service area details</li>
                <li>Immediate payment to begin work</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>Important Notes</h5>
            <ul>
                <li>Zero revisions before launch - customization is your responsibility</li>
                <li>You\'ll need WordPress/Elementor knowledge to customize</li>
                <li>Changes after launch are charged at $175/hour</li>
                <li>Rank Math SEO plugin included</li>
                <li>Additional plugin installation not permitted</li>
            </ul>
        </div>
    </div>
</div>');
    
    // Output the entire content as HTML
    echo wp_kses_post($content);
    ?>
    
    <div class="terms-link">
        <a href="#" onclick="openTermsModal(); return false;" class="terms-modal-trigger">View Full Terms & Conditions</a>
    </div>
    
    <label class="checkbox-card">
        <input type="checkbox" name="accept_terms" value="accepted" required>
        <span>I Accept the Terms & Conditions</span>
    </label>

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

    <div class="validation-message" id="validationMessage" style="display: none;">
        <p>Please complete all required fields to continue:</p>
        <ul id="validationList"></ul>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-next">Next</button>
    </div>
</div>

<!-- Terms Modal -->
<div id="termsModal" class="docket-modal" style="display: none;">
    <div class="docket-modal-content">
        <div class="docket-modal-header">
            <h2>Fast Build Terms & Conditions</h2>
            <span class="docket-modal-close">&times;</span>
        </div>
        <div class="docket-modal-body">
            <p>These are the Fast Build specific terms and conditions. By proceeding, you agree to all terms.</p>
            
            <h3>Fast Build Overview</h3>
            <p>Fast Build is designed for customers who want a quick website launch with minimal customization requirements.</p>
            
            <h3>Timeline</h3>
            <p>Your website will be completed within 3 business days after we receive all required information and payment.</p>
            
            <h3>Limitations</h3>
            <ul>
                <li>Zero revisions before launch</li>
                <li>Stock content and stock images will be used</li>
                <li>Customer is responsible for all post-launch customization</li>
                <li>WordPress/Elementor knowledge required for modifications</li>
            </ul>
            
            <h3>Payment Terms</h3>
            <p>Full payment is required immediately upon order submission to begin work.</p>
            
            <h3>Post-Launch Support</h3>
            <p>After launch, any changes or customizations are charged at $175/hour.</p>
            
            <h3>Content Management</h3>
            <p>The website will be built using WordPress with Elementor page builder. You will receive backend access upon completion.</p>
            
            <h3>Customer Requirements</h3>
            <p>By choosing Fast Build, you confirm that you have WordPress/Elementor experience or are willing to learn for post-launch customization.</p>
            
            <h3>Limitation of Liability</h3>
            <p>In no event shall Docket be liable for any indirect, incidental, special, or consequential damages arising out of or related to this agreement.</p>
            
            <h3>Termination</h3>
            <p>Due to the expedited nature of Fast Build, cancellations are not accepted once work begins.</p>
        </div>
    </div>
</div>

<script>
// Modal functionality (inline to ensure it's available immediately)
function openTermsModal() {
    document.getElementById('termsModal').style.display = 'block';
}

// Close modal when clicking X
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('docket-modal-close')) {
        document.getElementById('termsModal').style.display = 'none';
    }
});

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    var modal = document.getElementById('termsModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
</script>
