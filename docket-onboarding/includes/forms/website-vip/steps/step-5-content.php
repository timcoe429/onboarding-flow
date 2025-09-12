<!-- Step 5: Website Content -->
<div class="form-step" data-step="5">
    <h2>Website Content Information</h2>
    <p class="step-subtitle">Customize your template with content specific to your business</p>
    
    <div class="info-box mb-30">
        <p>We want to give you the option to customize your template with content geared specifically to your business.</p>
        <p>If you'd rather suggest edits to the stock content we provide during the review process, or customize your content yourself post-launch, no worries!</p>
    </div>

    <div class="form-field">
        <label>Do you want to give our team website content at this time? *</label>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="do_you_want_to_give_our_team_website_content_at_this_time" value="Yes" required>
                <div class="radio-card-content">
                    <strong>Yes</strong>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="do_you_want_to_give_our_team_website_content_at_this_time" value="No — Use all stock content for my website draft" required>
                <div class="radio-card-content">
                    <strong>No — Use all stock content for my website draft</strong>
                </div>
            </label>
        </div>
    </div>

    <!-- Content Fields (shown conditionally) -->
    <div id="contentFields" style="display: none;">
        <!-- Company Tagline -->
        <div class="form-field">
            <label>9.1 Do you want to provide a company tagline? *</label>
            <div class="radio-inline">
                <label>
                    <input type="radio" name="do_you_want_to_provide_a_company_tagline" value="Yes">
                    <span>Yes</span>
                </label>
                <label>
                    <input type="radio" name="do_you_want_to_provide_a_company_tagline" value="No — I'm okay with this being blank or stock content">
                    <span>No — I'm okay with this being blank or stock content</span>
                </label>
            </div>
        </div>
        
        <div class="form-field" id="taglineField" style="display: none;">
            <label>Company Tagline (used on your Home page)</label>
            <input type="text" name="company_tagline" maxlength="65">
        </div>

        <!-- Template 4 Only Content -->
        <div class="template4-only" style="display: none;">
            <!-- FAQs -->
            <div class="form-field">
                <label>9.2 Do you want to provide 5 company FAQ's? *</label>
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="do_you_want_to_provide_5_company_faqs" value="Yes">
                        <span>Yes</span>
                    </label>
                    <label>
                        <input type="radio" name="do_you_want_to_provide_5_company_faqs" value="No — use the stock FAQ's for my website draft">
                        <span>No — use the stock FAQ's for my website draft</span>
                    </label>
                </div>
            </div>
            
            <div class="form-field" id="faqField" style="display: none;">
                <label>Company FAQ's (5) *</label>
                <textarea name="company_faqs" rows="6" placeholder="Please only provide 5 FAQ's. Please include both the question AND the answer."></textarea>
            </div>

            <!-- Benefits -->
            <div class="form-field">
                <label>9.3 Do you want to provide 5 Benefits/What We Do Q+A's? *</label>
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="benefits_QA" value="Yes">
                        <span>Yes</span>
                    </label>
                    <label>
                        <input type="radio" name="benefits_QA" value="No — Use stock content for my website draft">
                        <span>No — Use stock content for my website draft</span>
                    </label>
                </div>
            </div>
            
            <div class="form-field" id="benefitsField" style="display: none;">
                <label>Benefits/What We Do (5) *</label>
                <textarea name="benefits_what_we_do" rows="6" placeholder="Please only provide 5 Benefits/What We Do's. Please include both the question AND the answer."></textarea>
            </div>
        </div>

        <!-- Footer -->
        <div class="form-field">
            <label>9.4 Do you want to give a company summary for your website footer? *</label>
            <div class="radio-inline">
                <label>
                    <input type="radio" name="website_footer" value="Yes">
                    <span>Yes</span>
                </label>
                <label>
                    <input type="radio" name="website_footer" value="No — Use stock content for my website draft">
                    <span>No — Use stock content for my website draft</span>
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
        <label>PRO: Select the focus of your 4 blogs we will add to your website. These blogs are intended to help with content marketing, and are generalized to cast a wider net of information for your potential customers. *</label>
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
        <label>10. What are the 9 main areas you service?</label>
        <p class="field-note">These can be cities, counties, regions, etc. We can include up to 9 on your website. If including cities, please list both the city name AND the state abbreviation (example: Denver, CO).</p>
        <div class="service-areas-grid">
            <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="service-area-field">
                <label><?php echo $i; ?>.</label>
                <input type="text" name="servicearea<?php echo $i; ?>">
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
