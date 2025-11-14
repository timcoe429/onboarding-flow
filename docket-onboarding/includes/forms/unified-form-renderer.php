<?php
/**
 * Unified Form Renderer
 * Single function to render any form type based on configuration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a form based on form type and configuration
 * 
 * @param string $form_type Form type (fast-build, standard-build, website-vip)
 * @param array $form_data Form data passed from onboarding (plan, management, buildType)
 * @return void Outputs HTML
 */
function docket_render_form($form_type, $form_data = array()) {
    // Get configuration for this form type
    $config = docket_get_form_config_by_type($form_type);
    
    if (!$config) {
        echo '<p>Error: Invalid form type.</p>';
        return;
    }
    
    // Extract form data
    $plan_type = isset($form_data['plan']) ? $form_data['plan'] : '';
    $management_type = isset($form_data['management']) ? $form_data['management'] : '';
    $build_type = isset($form_data['buildType']) ? $form_data['buildType'] : '';
    
    // Format plan type based on config
    if ($config['plan_type_format'] === 'ucfirst') {
        $plan_type_formatted = ucfirst($plan_type);
    } else {
        $plan_type_formatted = $plan_type;
    }
    
    // Format management type based on config
    if ($config['management_type_format'] === 'fixed') {
        $management_type_formatted = $config['management_type_value'];
    } elseif ($config['management_type_format'] === 'ucfirst') {
        $management_type_formatted = ucfirst($management_type);
    } else {
        $management_type_formatted = $management_type;
    }
    
    // Calculate progress percentage (12.5% per step for 8 steps)
    $step_count = count($config['steps']);
    $progress_percentage = 100 / $step_count;
    
    ?>
    <div class="<?php echo esc_attr($config['form_class']); ?>" id="<?php echo esc_attr($config['container_id']); ?>">
        <!-- Clean Progress Bar -->
        <div class="docket-form-progress">
            <div class="docket-progress-track">
                <div class="docket-progress-fill" data-progress="<?php echo esc_attr($progress_percentage); ?>"></div>
            </div>
            <div class="docket-progress-dots">
                <?php foreach ($config['steps'] as $step_num): ?>
                    <span class="<?php echo $step_num === 1 ? 'active' : ''; ?>" data-step="<?php echo esc_attr($step_num); ?>"><?php echo esc_html($step_num); ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <form id="<?php echo esc_attr($config['form_id']); ?>" method="post" enctype="multipart/form-data">
            <!-- Hidden fields for onboarding data -->
            <input type="hidden" name="docket_plan_type" value="<?php echo esc_attr($plan_type_formatted); ?>">
            <input type="hidden" name="docket_management_type" value="<?php echo esc_attr($management_type_formatted); ?>">
            <input type="hidden" name="docket_build_type" value="<?php echo esc_attr($build_type); ?>">
            <input type="hidden" name="select_your_docket_plan" value="<?php echo esc_attr($plan_type_formatted); ?>">

            <!-- Add WordPress nonce field -->
            <?php wp_nonce_field('docket_onboarding_nonce', 'nonce'); ?>

            <!-- Include form steps -->
            <?php 
            $steps_path = docket_get_form_steps_path($form_type);
            $shared_steps_path = DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/shared/steps/';
            
            // Steps that are shared across all form types
            $shared_steps = array('step-1-terms.php', 'step-2-contact.php', 'step-3-template-info.php', 'step-4-template-select.php', 'step-5-content.php', 'step-6-branding.php', 'step-7-rentals.php', 'step-8-marketing.php');
            
            // Set global form type so step files can access it
            global $docket_current_form_type;
            $docket_current_form_type = $form_type;
            
            foreach ($config['steps'] as $step_num) {
                if (isset($config['step_files'][$step_num])) {
                    $step_filename = $config['step_files'][$step_num];
                    
                    // Check if this step is shared, otherwise use form-specific path
                    if (in_array($step_filename, $shared_steps)) {
                        $step_file = $shared_steps_path . $step_filename;
                    } else {
                        $step_file = $steps_path . $step_filename;
                    }
                    
                    if (file_exists($step_file)) {
                        include $step_file;
                    } else {
                        error_log("Docket Onboarding: Step file not found: {$step_file}");
                    }
                }
            }
            
            // Clean up global
            unset($GLOBALS['docket_current_form_type']);
            ?>
        </form>

        <!-- Success Screen -->
        <div class="form-success" style="display: none;">
            <div class="success-icon">✓</div>
            <h2><?php echo esc_html($config['success_title']); ?></h2>
            <p><?php echo esc_html($config['success_message']); ?></p>
            <p class="success-note"><?php echo esc_html($config['success_note']); ?></p>
        </div>
    </div>
    
    <!-- Unified JavaScript with form-specific configuration -->
    <script>
        window.docketFormConfig = {
            formId: '<?php echo esc_js('#' . $config['form_id']); ?>',
            formType: '<?php echo esc_js($form_type); ?>',
            actionName: '<?php echo esc_js($config['action_name']); ?>',
            stepCount: <?php echo esc_js(count($config['steps'])); ?>
        };
    </script>
    <?php
}

