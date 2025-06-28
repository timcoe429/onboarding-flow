<?php
/**
 * Form Handler for Docket Onboarding
 * Handles AJAX requests and form integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the fast build form file
if (file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/fast-build/fast-build-form.php')) {
    require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/fast-build/fast-build-form.php';
}

// Include the standard build form file
if (file_exists(DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/standard-build-form.php')) {
    require_once DOCKET_ONBOARDING_PLUGIN_DIR . 'includes/forms/standard-build/standard-build-form.php';
}

/**
 * Handle AJAX request to load fast build form
 */
add_action('wp_ajax_docket_load_fast_build_form', 'docket_ajax_load_fast_build_form');
add_action('wp_ajax_nopriv_docket_load_fast_build_form', 'docket_ajax_load_fast_build_form');

function docket_ajax_load_fast_build_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get form data
    $form_data = array(
        'plan' => sanitize_text_field($_POST['plan']),
        'management' => sanitize_text_field($_POST['management']),
        'buildType' => sanitize_text_field($_POST['buildType'])
    );
    
    // Start output buffering
    ob_start();
    
    // Add CSS link
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/fast-build/fast-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
    
    // Add JavaScript link
    $js_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/fast-build/fast-build-form.js?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<script src="' . esc_url($js_url) . '"></script>';
    
    // Render the fast build form
    if (function_exists('docket_render_fast_build_form')) {
        docket_render_fast_build_form($form_data);
    } else {
        echo '<p>Error: Fast build form not found.</p>';
    }
    
    // Get the output
    $form_html = ob_get_clean();
    
    // Apply filter for additional modifications
    $form_html = apply_filters('docket_fast_build_form_response', $form_html);
    
    wp_send_json_success(array(
        'form_html' => $form_html,
        'message' => 'Form loaded successfully'
    ));
    
    wp_die();
}

/**
 * Handle AJAX request to load standard build form
 */
add_action('wp_ajax_docket_load_standard_build_form', 'docket_ajax_load_standard_build_form');
add_action('wp_ajax_nopriv_docket_load_standard_build_form', 'docket_ajax_load_standard_build_form');

function docket_ajax_load_standard_build_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get form data
    $form_data = array(
        'plan' => sanitize_text_field($_POST['plan']),
        'management' => sanitize_text_field($_POST['management']),
        'buildType' => sanitize_text_field($_POST['buildType'])
    );
    
    // Start output buffering
    ob_start();
    
    // Add CSS link
    $css_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/standard-build/standard-build-form.css?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '" type="text/css" media="all" />';
    
    // Add JavaScript link
    $js_url = DOCKET_ONBOARDING_PLUGIN_URL . 'includes/forms/standard-build/standard-build-form.js?ver=' . DOCKET_ONBOARDING_VERSION;
    echo '<script src="' . esc_url($js_url) . '"></script>';
    
    // Add script to initialize the form after loading
    echo '<script>
        // Wait for script to load then initialize
        function waitForScript() {
            if (typeof jQuery !== "undefined" && jQuery("#standardBuildForm").length > 0) {
                // Initialize the form directly since it\'s loaded via AJAX
                jQuery(function($) {
                    // Copy the initialization code from standard-build-form.js
                    var currentStep = 1;
                    var totalSteps = 8;
                    
                    // Initialize modal functionality
                    window.openTermsModal = function() {
                        var modal = document.getElementById("termsModal");
                        if (modal) {
                            modal.style.display = "block";
                        }
                    }
                    
                    // Close modal when clicking X or outside
                    $(document).on("click", ".docket-modal-close, .docket-modal", function(e) {
                        if (e.target === this) {
                            $("#termsModal").hide();
                        }
                    });
                    
                    // Prevent modal content clicks from closing
                    $(document).on("click", ".docket-modal-content", function(e) {
                        e.stopPropagation();
                    });
                    
                    // Rest of form initialization
                    initializeFormHandlers();
                });
            } else {
                setTimeout(waitForScript, 100);
            }
        }
        
        waitForScript();
        
        function initializeFormHandlers() {
            // Form navigation and validation code
            var $ = jQuery;
            var form = $("#standardBuildForm");
            var currentStep = 1;
            var totalSteps = 8;
            
            // Navigation handlers
            form.find(".btn-next").off("click").on("click", function() {
                if (validateStep(currentStep)) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
            
            form.find(".btn-prev").off("click").on("click", function() {
                currentStep--;
                showStep(currentStep);
            });
            
            function showStep(step) {
                $(".form-step").removeClass("active");
                $(".form-step[data-step=\'" + step + "\']").addClass("active");
                updateProgressBar(step);
            }
            
            function updateProgressBar(step) {
                var progress = (step / totalSteps) * 100;
                $(".docket-progress-fill").css("width", progress + "%");
                $(".docket-progress-dots span").removeClass("active completed");
                for (var i = 1; i <= step; i++) {
                    if (i < step) {
                        $(".docket-progress-dots span[data-step=\'" + i + "\']").addClass("completed");
                    } else {
                        $(".docket-progress-dots span[data-step=\'" + i + "\']").addClass("active");
                    }
                }
            }
            
            function validateStep(step) {
                var isValid = true;
                var currentStepElement = $(".form-step[data-step=\'" + step + "\']");
                
                currentStepElement.find("input[required], select[required], textarea[required]").each(function() {
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        isValid = false;
                        return false;
                    }
                });
                
                return isValid;
            }
        }
    </script>';
    
    // Render the standard build form
    if (function_exists('docket_render_standard_build_form')) {
        docket_render_standard_build_form($form_data);
    } else {
        echo '<p>Error: Standard build form not found.</p>';
    }
    
    // Get the output
    $form_html = ob_get_clean();
    
    // Apply filter for additional modifications
    $form_html = apply_filters('docket_standard_build_form_response', $form_html);
    
    wp_send_json_success(array(
        'form_html' => $form_html,
        'message' => 'Form loaded successfully'
    ));
    
    wp_die();
}

/**
 * Handle AJAX request to load Avada form (keeping for backward compatibility)
 */
add_action('wp_ajax_docket_load_avada_form', 'docket_load_avada_form');
add_action('wp_ajax_nopriv_docket_load_avada_form', 'docket_load_avada_form');

function docket_load_avada_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Get form ID
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    
    if (!$form_id) {
        wp_send_json_error(array('message' => 'Invalid form ID'));
        wp_die();
    }
    
    // Fetch the Avada form shortcode
    $form_html = do_shortcode('[fusion_form form_post_id="' . $form_id . '" /]');
    
    if (empty($form_html)) {
        wp_send_json_error(array('message' => 'Form not found'));
        wp_die();
    }
    
    wp_send_json_success(array(
        'form_html' => $form_html,
        'message' => 'Form loaded successfully'
    ));
    
    wp_die();
}

/**
 * Handle form submission
 */
add_action('wp_ajax_docket_submit_onboarding', 'docket_handle_form_submission');
add_action('wp_ajax_nopriv_docket_submit_onboarding', 'docket_handle_form_submission');

function docket_handle_form_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docket_onboarding_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }
    
    // Process the form data
    $form_data = array();
    
    // Sanitize all form fields
    foreach ($_POST as $key => $value) {
        if ($key !== 'nonce' && $key !== 'action') {
            if (is_array($value)) {
                $form_data[$key] = array_map('sanitize_text_field', $value);
            } else {
                $form_data[$key] = sanitize_text_field($value);
            }
        }
    }
    
    // You can add custom processing here
    // For example: save to database, send emails, create user accounts, etc.
    
    // Example: Save to options table
    $submission_id = time();
    update_option('docket_submission_' . $submission_id, $form_data);
    
    // Send success response
    wp_send_json_success(array(
        'message' => 'Form submitted successfully',
        'submission_id' => $submission_id,
        'redirect_url' => home_url('/thank-you/') // Adjust as needed
    ));
    
    wp_die();
}

?>
