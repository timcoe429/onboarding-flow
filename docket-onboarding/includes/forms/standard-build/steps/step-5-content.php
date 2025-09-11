<!-- Step 5: Website Content -->
<div class="form-step" data-step="5">
    <h2>Website Content Information</h2>
    <p class="step-subtitle">Customize your template with content specific to your business</p>
    
    <div class="info-box mb-30">
        <p><strong>We want to give you the option to customize your template with content geared specifically to your business.</strong></p>
        <p>If you'd rather suggest edits to the stock content during review, or customize post-launch, no worries!</p>
    </div>

    <div class="form-field">
        <label>Do you want to provide website content at this time? *</label>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="provide_content_now" value="Yes" required>
                <div class="radio-card-content">
                    <strong>Yes</strong>
                    <span>I'll provide custom content now</span>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="provide_content_now" value="No" required>
                <div class="radio-card-content">
                    <strong>No</strong>
                    <span>Use all stock content for my website draft</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Content Fields (shown conditionally) -->
    <div id="contentFields" style="display: none;">
        <!-- Company Tagline -->
        <div class="form-field">
            <label>Do you want to provide a company tagline? *</label>
            <div class="radio-inline">
                <label>
                    <input type="radio" name="provide_tagline" value="Yes">
                    <span>Yes</span>
                </label>
                <label>
                    <input type="radio" name="provide_tagline" value="No">
                    <span>No - I'm okay with blank or stock content</span>
                </label>
            </div>
        </div>
        
        <div class="form-field" id="taglineField" style="display: none;">
            <label>Company Tagline (used on Home page)</label>
            <input type="text" name="company_tagline" maxlength="65">
        </div>

        <!-- FAQs (Template 4 only) -->
        <div class="template4-only" style="display: none;">
            <div class="form-field">
                <label>Do you want to provide 5 company FAQ's? *</label>
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="provide_faqs" value="Yes">
                        <span>Yes</span>
                    </label>
                    <label>
                        <input type="radio" name="provide_faqs" value="No">
                        <span>No - use stock FAQ's</span>
                    </label>
                </div>
            </div>
            
            <div class="form-field" id="faqField" style="display: none;">
                <label>Company FAQ's (5) *</label>
                <textarea name="company_faqs" rows="6" placeholder="Please provide 5 FAQ's. Include both the question AND the answer."></textarea>
            </div>
        </div>

        <!-- Benefits -->
        <div class="form-field">
            <label>Do you want to provide 5 Benefits/What We Do Q+A's? *</label>
            <div class="radio-inline">
                <label>
                    <input type="radio" name="provide_benefits" value="Yes">
                    <span>Yes</span>
                </label>
                <label>
                    <input type="radio" name="provide_benefits" value="No">
                    <span>No - use stock content</span>
                </label>
            </div>
        </div>
        
        <div class="form-field" id="benefitsField" style="display: none;">
            <label>Benefits/What We Do (5) *</label>
            <textarea name="benefits_what_we_do" rows="6" placeholder="Please provide 5 Benefits/What We Do's. Include both the question AND the answer."></textarea>
        </div>

        <!-- Footer -->
        <div class="form-field">
            <label>Do you want to provide a company summary for website footer? *</label>
            <div class="radio-inline">
                <label>
                    <input type="radio" name="provide_footer" value="Yes">
                    <span>Yes</span>
                </label>
                <label>
                    <input type="radio" name="provide_footer" value="No">
                    <span>No - use stock content</span>
                </label>
            </div>
        </div>
        
        <div class="form-field" id="footerField" style="display: none;">
            <label>Company Summary - Website Footer</label>
            <input type="text" name="website_footer" maxlength="65">
        </div>
    </div>

    <!-- PRO Plan Blog Focus -->
    <?php if ($plan_type === 'pro'): ?>
    <div class="form-field">
        <label>Select the focus of your 4 blogs *</label>
        <p class="field-note">These blogs help with content marketing and cast a wider net for potential customers.</p>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="blog_focus" value="Residential Dumpster Rentals" required>
                <div class="radio-card-content">Residential Dumpster Rentals</div>
            </label>
            <label class="radio-card">
                <input type="radio" name="blog_focus" value="Commercial Dumpster Rentals" required>
                <div class="radio-card-content">Commercial Dumpster Rentals</div>
            </label>
            <label class="radio-card">
                <input type="radio" name="blog_focus" value="Mix of Both" required>
                <div class="radio-card-content">Mix of Both</div>
            </label>
        </div>
    </div>
    <?php endif; ?>

    <!-- Service Areas -->
    <div class="form-field">
        <label>What are the 9 main areas you service?</label>
        <p class="field-note">These can be cities, counties, regions, etc. Include city name AND state abbreviation (e.g., Denver, CO)</p>
        <div class="service-areas-grid">
            <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="service-area-field">
                <label><?php echo $i; ?>.</label>
                <input type="text" name="servicearea<?php echo $i; ?>" placeholder="City, State">
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
