<!-- Step 8: Company Marketing -->
<div class="form-step" data-step="8">
    <?php 
    // Get form type from context (passed via unified renderer)
    global $docket_current_form_type;
    $form_type = isset($docket_current_form_type) ? $docket_current_form_type : 'standard-build';
    
    // Determine form-specific features
    $show_social_media = ($form_type !== 'fast-build'); // Standard Build and Website VIP show this
    $show_reviews = ($form_type !== 'fast-build'); // Standard Build and Website VIP show this
    ?>
    
    <h2>Company Marketing</h2>
    <p class="step-subtitle">Final questions about your marketing needs</p>
    
    <div class="form-field">
        <label>Are you currently working with an SEO or Marketing agency? *</label>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="marketing_agency" value="Yes" required>
                <div class="radio-card-content">
                    <strong>Yes</strong>
                    <span>
                        <?php if ($form_type === 'website-vip'): ?>
                            I'll make sure they know about the plugin and back-end access limitations of the website
                        <?php else: ?>
                            I'll inform them of the website terms & conditions
                        <?php endif; ?>
                    </span>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="marketing_agency" value="Soon" required>
                <div class="radio-card-content">
                    <strong><?php echo ($form_type === 'fast-build') ? 'Planning to' : 'I will be soon'; ?></strong>
                    <span>
                        <?php if ($form_type === 'website-vip'): ?>
                            I'm planning on working with an external agency in the future and will let them know about the plugin and back-end access limitations of the website
                        <?php else: ?>
                            I'll work with one in the future & inform them about the website terms & conditions
                        <?php endif; ?>
                    </span>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="marketing_agency" value="No" required>
                <div class="radio-card-content">
                    <strong>No</strong>
                    <span>
                        Not using external marketing
                    </span>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="marketing_agency" value="Interested" required>
                <div class="radio-card-content">
                    <strong><?php echo ($form_type === 'fast-build') ? 'Interested in Docket\'s Services' : 'No - but interested'; ?></strong>
                    <span>
                        <?php if ($form_type === 'website-vip'): ?>
                            I would be interested in information on Docket's digital marketing services in the future
                        <?php else: ?>
                            I'd like to learn more about your marketing services like WebsiteVIP and Advanced SEO
                        <?php endif; ?>
                    </span>
                </div>
            </label>
        </div>
    </div>
    
    <?php if ($show_social_media): ?>
    <div class="form-field">
        <label>Social Media Links</label>
        <p class="field-note">Please provide the exact URLs for your social media profiles</p>
        <div class="social-media-grid">
            <div class="social-media-field">
                <label>Facebook</label>
                <input type="url" name="facebook" placeholder="https://www.facebook.com/YourPage">
            </div>
            <div class="social-media-field">
                <label>Instagram</label>
                <input type="url" name="instagram" placeholder="https://www.instagram.com/YourProfile">
            </div>
            <div class="social-media-field">
                <label>X (Twitter)</label>
                <input type="url" name="twitter" placeholder="https://www.x.com/YourProfile">
            </div>
            <div class="social-media-field">
                <label>YouTube</label>
                <input type="url" name="YouTube" placeholder="https://www.youtube.com/@YourChannel">
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($show_reviews): ?>
    <div class="form-field">
        <label>Any reviews or testimonials you'd like to add?</label>
        <textarea name="reviews_testimonials" rows="4" placeholder="Please add both the review and the first name and last initial of the reviewer. We cannot pull reviews directly from Google or other platforms."></textarea>
    </div>
    <?php endif; ?>

    <div class="form-nav">
        <button type="button" class="btn-prev">Back</button>
        <button type="submit" class="btn-submit">Submit Order</button>
    </div>
</div>

