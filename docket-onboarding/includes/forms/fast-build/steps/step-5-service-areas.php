<!-- Step 5: Service Areas & Blog Focus -->
<div class="form-step" data-step="5">
    <h2>Service Areas</h2>
    <p class="step-subtitle">List up to 9 areas you service (cities, counties, regions)</p>
    
    <?php if ($plan_type === 'pro'): ?>
    <div class="form-field mb-30">
        <label>Blog Content Focus *</label>
        <p class="field-note">Since you selected the Pro plan, choose your blog content focus</p>
        <div class="radio-group">
            <label class="radio-card">
                <input type="radio" name="blog_focus" value="Residential" required>
                <div class="radio-card-content">Residential Focus</div>
            </label>
            <label class="radio-card">
                <input type="radio" name="blog_focus" value="Commercial" required>
                <div class="radio-card-content">Commercial Focus</div>
            </label>
            <label class="radio-card">
                <input type="radio" name="blog_focus" value="Both" required>
                <div class="radio-card-content">Mix of Both</div>
            </label>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="service-areas-grid">
        <?php for ($i = 1; $i <= 9; $i++): ?>
        <div class="service-area-field">
            <label><?php echo $i; ?>.</label>
            <input type="text" name="servicearea<?php echo $i; ?>" placeholder="City, State">
        </div>
        <?php endfor; ?>
    </div>

    <div class="form-nav">
        <button type="button" class="btn-next">Next</button>
    </div>
</div>
