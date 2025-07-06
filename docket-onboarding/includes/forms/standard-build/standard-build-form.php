<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the standard build form
 */
function docket_render_standard_build_form($form_data = array()) {
    // Extract form data passed from onboarding
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : '';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    ?>
    
    <div class="docket-standard-form" id="docketStandardBuildForm">
        <!-- Clean Progress Bar -->
        <div class="docket-form-progress">
            <div class="docket-progress-track">
                <div class="docket-progress-fill" data-progress="12.5"></div>
            </div>
            <div class="docket-progress-dots">
                <span class="active" data-step="1">1</span>
                <span data-step="2">2</span>
                <span data-step="3">3</span>
                <span data-step="4">4</span>
                <span data-step="5">5</span>
                <span data-step="6">6</span>
                <span data-step="7">7</span>
                <span data-step="8">8</span>
            </div>
        </div>

        <form id="standardBuildForm" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">
            <input type="hidden" name="docket_management_type" value="<?php echo esc_attr($management_type); ?>">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr(ucfirst($plan_type)); ?>">

            <!-- Add WordPress nonce field -->
<?php wp_nonce_field('docket_onboarding_nonce', 'nonce'); ?>
            <!-- Include form steps -->
            <?php 
            $steps_path = DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/steps/';
            
            include $steps_path . 'step-1-terms.php';
            include $steps_path . 'step-2-contact.php';
            include $steps_path . 'step-3-template-info.php';
            include $steps_path . 'step-4-template-select.php';
            include $steps_path . 'step-5-content.php';
            include $steps_path . 'step-6-branding.php';
            include $steps_path . 'step-7-rentals.php';
            include $steps_path . 'step-8-marketing.php';
            ?>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2>Order Submitted!</h2>
            <p>Thank you! We'll start building your website right away.</p>
            <p class="success-note">You'll receive a confirmation email shortly with next steps.</p>
        </div>
    </div>
    <?php
}

/**
 * AJAX Handler for form submission
 */
add_action('wp_ajax_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');
add_action('wp_ajax_nopriv_docket_submit_standard_build_form', 'docket_handle_standard_build_submission');
