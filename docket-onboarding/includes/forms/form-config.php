<?php
/**
 * Form Configuration
 * Centralized configuration for all form types
 * Single source of truth for form differences
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get form configuration for all form types
 * 
 * @return array Configuration array for all forms
 */
function docket_get_form_config() {
    return array(
        'fast-build' => array(
            'form_id' => 'fastBuildForm',
            'form_class' => 'docket-fast-form',
            'container_id' => 'docketFastBuildForm',
            'steps' => array(1, 2, 3, 4, 5, 6, 7, 8),
            'step_files' => array(
                1 => 'step-1-terms.php',
                2 => 'step-2-contact.php',
                3 => 'step-3-template-info.php',
                4 => 'step-4-template-select.php',
                5 => 'step-5-service-areas.php',
                6 => 'step-6-branding.php',
                7 => 'step-7-rentals.php',
                8 => 'step-8-marketing.php'
            ),
            'success_title' => 'Fast Build Order Submitted!',
            'success_message' => 'Thank you! Your Fast Build website will be ready in 3 business days.',
            'success_note' => 'You\'ll receive a confirmation email shortly with next steps.',
            'action_name' => 'docket_submit_fast_build_form',
            'plan_type_format' => 'raw', // Use raw value (no ucfirst)
            'management_type_format' => 'raw', // Use raw value
        ),
        'standard-build' => array(
            'form_id' => 'standardBuildForm',
            'form_class' => 'docket-standard-form',
            'container_id' => 'docketStandardBuildForm',
            'steps' => array(1, 2, 3, 4, 5, 6, 7, 8),
            'step_files' => array(
                1 => 'step-1-terms.php',
                2 => 'step-2-contact.php',
                3 => 'step-3-template-info.php',
                4 => 'step-4-template-select.php',
                5 => 'step-5-content.php',
                6 => 'step-6-branding.php',
                7 => 'step-7-rentals.php',
                8 => 'step-8-marketing.php'
            ),
            'success_title' => 'Order Submitted!',
            'success_message' => 'Thank you! We\'ll start building your website right away.',
            'success_note' => 'You\'ll receive a confirmation email shortly with next steps.',
            'action_name' => 'docket_submit_standard_build_form',
            'plan_type_format' => 'ucfirst', // Use ucfirst($plan_type)
            'management_type_format' => 'raw', // Use raw value
        ),
        'website-vip' => array(
            'form_id' => 'websiteVipForm',
            'form_class' => 'docket-vip-form',
            'container_id' => 'docketWebsiteVipForm',
            'steps' => array(1, 2, 3, 4, 5, 6, 7, 8),
            'step_files' => array(
                1 => 'step-1-terms.php',
                2 => 'step-2-contact.php',
                3 => 'step-3-template-info.php',
                4 => 'step-4-template-select.php',
                5 => 'step-5-content.php',
                6 => 'step-6-branding.php',
                7 => 'step-7-rentals.php',
                8 => 'step-8-marketing.php'
            ),
            'success_title' => 'Website VIP Order Submitted!',
            'success_message' => 'Thank you! Your Website VIP order has been received.',
            'success_note' => 'Our team will contact you shortly to discuss your WebsiteVIP plan upgrade and next steps.',
            'action_name' => 'docket_submit_website_vip_form',
            'plan_type_format' => 'ucfirst', // Use ucfirst($plan_type)
            'management_type_format' => 'fixed', // Always use "WebsiteVIP"
            'management_type_value' => 'WebsiteVIP', // Fixed value for WebsiteVIP
        ),
    );
}

/**
 * Get configuration for a specific form type
 * 
 * @param string $form_type Form type (fast-build, standard-build, website-vip)
 * @return array|false Configuration array or false if not found
 */
function docket_get_form_config_by_type($form_type) {
    $config = docket_get_form_config();
    return isset($config[$form_type]) ? $config[$form_type] : false;
}

/**
 * Get step count for a form type
 * 
 * @param string $form_type Form type
 * @return int Number of steps
 */
function docket_get_form_step_count($form_type) {
    $config = docket_get_form_config_by_type($form_type);
    return $config ? count($config['steps']) : 0;
}

/**
 * Get steps directory path for a form type
 * 
 * @param string $form_type Form type
 * @return string Path to steps directory
 */
function docket_get_form_steps_path($form_type) {
    return DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/' . $form_type . '/steps/';
}

