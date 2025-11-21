<!-- Step 1: Terms & Conditions -->
<div class="form-step active" data-step="1">
    <?php 
    // Get form type from context (passed via unified renderer)
    // If not available, try to determine from global or fallback
    global $docket_current_form_type;
    $form_type = isset($docket_current_form_type) ? $docket_current_form_type : 'standard-build';
    
    // Set default content based on form type
    $default_content = '';
    $modal_title = '';
    $show_wordpress_exp = false;
    
    switch ($form_type) {
        case 'fast-build':
            $default_content = '
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
</div>';
            $modal_title = 'Fast Build Terms & Conditions';
            $show_wordpress_exp = true;
            break;
            
        case 'website-vip':
            $default_content = '
<h2>Website with WebsiteVIP Terms & Conditions</h2>
<p>Please review and accept our terms for your WordPress experience with WebsiteVIP.</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting with WebsiteVIP</h5>
            <p>A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, with ongoing management by the Docket team for $299/month. You will not receive edit access to your website once it launches.</p>
        </div>
        
        <div class="terms-section">
            <h5>Timeline</h5>
            <p>Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.</p>
        </div>
        
        <div class="terms-section">
            <h5>What We Need From You</h5>
            <ul>
                <li>Business information and branding materials</li>
                <li>Service area details and pricing</li>
                <li>Photos and content for your website</li>
                <li>Timely feedback during the review process</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>WebsiteVIP Benefits</h5>
            <ul>
                <li>Completely managed by the Docket Team</li>
                <li>Unlimited edits</li>
                <li>AI Chat Bot, On-Page SEO, Location Pages, Analytics, and more</li>
                <li>You\'ll be contacted to discuss the WebsiteVIP plan upgrade after you submit this form</li>
            </ul>
        </div>
    </div>
</div>';
            $modal_title = 'Website Design & Development Terms & Conditions';
            break;
            
        default: // standard-build
            $default_content = '
<h2>Terms & Conditions</h2>
<p>Please review and accept our terms</p>

<div class="terms-box">
    <div class="terms-content">
        <div class="terms-section">
            <h5>What You\'re Getting</h5>
            <p>A professionally designed WordPress website built specifically for dumpster rental and junk removal businesses, including SEO optimization and mobile responsiveness.</p>
        </div>
        
        <div class="terms-section">
            <h5>Timeline</h5>
            <p>Your website will be completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.</p>
        </div>
        
        <div class="terms-section">
            <h5>What We Need From You</h5>
            <ul>
                <li>Business information and branding materials</li>
                <li>Service area details</li>
                <li>Photos and content for your website</li>
                <li>Dumpster rental/junk removal services information</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>What\'s Included</h5>
            <ul>
                <li>Rank Math SEO plugin included</li>
                <li>Additional plugin installation not permitted</li>
            </ul>
        </div>
        
        <div class="terms-section">
            <h5>Post-Launch Services</h5>
            <p>After launch, website access for editing and Rank Math SEO plugin configuration is shared. If you\'d like to have the Docket Team work on your website, you\'ll need to upgrade to the WebsiteVIP plan.</p>
        </div>
    </div>
</div>';
            $modal_title = 'Website Design & Development Terms & Conditions';
            break;
    }
    
    // Get the entire content for this step from database (with default fallback)
    $content = docket_get_form_content($form_type, 1, 'content', $default_content);
    
    // Output the entire content as HTML
    echo wp_kses_post($content);
    ?>
    
    <div class="terms-link">
        <a href="#" onclick="openTermsModal(); return false;" class="terms-modal-trigger">View Full Terms & Conditions</a>
    </div>
    
    <label class="checkbox-card">
        <input type="checkbox" name="accept_terms" <?php echo ($form_type === 'fast-build') ? 'value="accepted"' : ''; ?> required>
        <span>I Accept the Terms & Conditions</span>
    </label>

    <?php if ($show_wordpress_exp): ?>
    <!-- WordPress Experience (Fast Build only) -->
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
    <?php endif; ?>

    <?php if ($form_type !== 'website-vip'): ?>
    <div class="validation-message" id="validationMessage" style="display: none;">
        <p>Please complete all required fields to continue:</p>
        <ul id="validationList"></ul>
    </div>
    <?php endif; ?>

    <div class="form-nav">
        <button type="button" class="btn-next">Next</button>
    </div>
</div>

<!-- Terms Modal -->
<div id="termsModal" class="docket-modal" style="display: none;">
    <div class="docket-modal-content">
        <div class="docket-modal-header">
            <h2><?php echo esc_html($modal_title); ?></h2>
            <span class="docket-modal-close">&times;</span>
        </div>
        <div class="docket-modal-body">
            <?php
            // Modal content based on form type
            if ($form_type === 'fast-build'):
            ?>
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
            <?php else: ?>
            <p>These are the standard terms and conditions for Website Design and Development and apply to all contracts and all work that has been undertaken by Docket for its clients.</p>
            
            <p>By stating "I agree" via email, or making Payments, you are confirming that you can access and read and agree to all of this agreement and consent to use of this electronic method of contract acceptance under the U.S. Electronic Signatures in Global and National Commerce Act (E-SIGN).</p>
            
            <h3>Development</h3>
            <p>This Web Design Project will be developed using the latest version of WordPress HTML5 with standard WordPress Elements, unless specified otherwise.</p>
            
            <h3>Browser Compatibility</h3>
            <p>Designing a website to fully work in multiple browsers (and browser versions & resolutions) can require considerable, extra effort. It could also involve creating multiple versions of code/pages. Docket represents and warrants that the website we design for the latest browser versions for:</p>
            <ul>
                <li>Microsoft Edge</li>
                <li>Google Chrome</li>
                <li>Firefox</li>
                <li>Safari</li>
            </ul>
            
            <h3>Our Fees and Deposits</h3>
            <p>The total fee payable under our proposal is due immediately upon you instructing us to proceed with the website design and development work.</p>
            
            <h3>Payment Terms</h3>
            <p>Upon completion of the project, the website will be active on our live server for 72 hours. To activate the website under the client's domain, FULL payment is required.</p>
            
            <h3>Website Design Content Copyright</h3>
            <p>The Developer owns the legal copyright for all work completed during the project. Once the work has been completed and paid in full, the copyright ownership of the website shall be transferred to the Client.</p>
            
            <h3>Content Management System</h3>
            <p>The Developer will install and configure WordPress as the primary Content Management System (CMS) for the website.</p>
            
            <h3>Search Engine Optimization</h3>
            <p>The Developer will structure the website with SEO best practices including proper heading tags, meta descriptions, and URL structure.</p>
            
            <h3>Revisions</h3>
            <p>The project includes up to two rounds of revisions. Additional revisions will be billed at $175/hour.</p>
            
            <h3>Timeline</h3>
            <p>Standard build projects are typically completed within 21-30 business days. This timeframe covers creating your initial draft, reviewing and revising the site, finalizing content, and setting up domain access.</p>
            
            <h3>Client Responsibilities</h3>
            <p>The Client agrees to provide all necessary content, images, and feedback in a timely manner to ensure project completion within the stated timeline.</p>
            
            <h3>Limitation of Liability</h3>
            <p>In no event shall Docket be liable for any indirect, incidental, special, or consequential damages arising out of or related to this agreement.</p>
            
            <h3>Termination</h3>
            <p>Either party may terminate this agreement with written notice. The Client shall pay for all work completed up to the termination date.</p>
            
            <?php if ($form_type === 'standard-build'): ?>
            <h3>Governing Law</h3>
            <p>This agreement shall be governed by the laws of the state in which Docket operates.</p>
            <?php endif; ?>
            <?php endif; ?>
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

