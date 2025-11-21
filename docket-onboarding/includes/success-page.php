<?php
/**
 * Success Page Template
 * Renders a full-page success message after form submission
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the success page
 * 
 * @return void Outputs HTML
 */
function docket_render_success_page() {
    // Output CSS link directly since we're in a shortcode
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'assets/docket-forms-unified.css?ver=' . time();
    
    ?>
    <link rel="stylesheet" href="<?php echo esc_url($css_url); ?>" type="text/css" media="all" />
    <div class="docket-standard-form" id="docketSuccessPage">
        <div class="form-success-page">
            <div class="success-icon">✓</div>
            <h2>Thank You!</h2>
            <p class="success-message">Thank you for your submission. We will reach out to you when we feel like it to 48 hours for next steps.</p>
        </div>
    </div>
    <?php
}

