<!-- Step 1: Terms & Conditions -->
<div class="form-step active" data-step="1">
    <h2>Website VIP Terms & Conditions</h2>
    <p class="step-subtitle">Please review and accept our terms for WebsiteVIP service</p>
    
    <div class="terms-box">
        <div class="terms-content">
            <h4>Website Design Agreement Overview</h4>
            
            <div class="terms-section">
                <h5>What You're Getting with WebsiteVIP</h5>
                <p>A professionally designed WordPress website built specifically for dumpster rental businesses, with ongoing management by the Docket team for $299/month.</p>
            </div>
            
            <div class="terms-section">
                <h5>Timeline</h5>
                <p>Your website will be completed within 21-30 business days after we receive all required content and approvals.</p>
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
                    <li>You'll be contacted to discuss the WebsiteVIP plan upgrade after you submit this form</li>
                </ul>
            </div>
        </div>
        
        <div class="terms-link">
            <a href="#" onclick="openTermsModal(); return false;" class="terms-modal-trigger">View Full Terms & Conditions</a>
        </div>
        
        <label class="checkbox-card" style="margin-top: 16px;">
            <input type="checkbox" name="accept_terms_&_conditions" required>
            <span>I Accept the Terms & Conditions</span>
        </label>
    </div>
    
    <div class="form-nav">
        <button type="button" class="btn-next">Next</button>
    </div>
</div>

<!-- Terms Modal -->
<div id="termsModal" class="docket-modal" style="display: none;">
    <div class="docket-modal-content">
        <div class="docket-modal-header">
            <h2>Website Design & Development Terms & Conditions</h2>
            <span class="docket-modal-close">&times;</span>
        </div>
        <div class="docket-modal-body">
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
            <p>Standard build projects are typically completed within 21-30 business days from receipt of all required content and materials.</p>
            
            <h3>Client Responsibilities</h3>
            <p>The Client agrees to provide all necessary content, images, and feedback in a timely manner to ensure project completion within the stated timeline.</p>
            
            <h3>Limitation of Liability</h3>
            <p>In no event shall Docket be liable for any indirect, incidental, special, or consequential damages arising out of or related to this agreement.</p>
            
            <h3>Termination</h3>
            <p>Either party may terminate this agreement with written notice. The Client shall pay for all work completed up to the termination date.</p>
            
            <h3>Governing Law</h3>
            <p>This agreement shall be governed by the laws of the state in which Docket operates.</p>
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
